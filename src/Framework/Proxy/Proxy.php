<?php

namespace Perfumer\Framework\Proxy;

use Perfumer\Component\Container\Container;
use Perfumer\Component\Container\Exception\NotFoundException;
use Perfumer\Component\Endpoint\AbstractEndpoint;
use Perfumer\Component\Endpoint\EndpointGenerator;
use Perfumer\Framework\Controller\ControllerInterface;
use Perfumer\Framework\Controller\Module;
use Perfumer\Framework\Gateway\GatewayInterface;
use Perfumer\Framework\Gateway\HttpGateway;
use Perfumer\Framework\Router\RouterInterface;
use Perfumer\Framework\Router\RouterInterface as Router;
use Perfumer\Framework\Proxy\Exception\ForwardException;
use Perfumer\Framework\Proxy\Exception\ProxyException;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Output\ConsoleOutput;

class Proxy
{
    protected Container $container;

    protected GatewayInterface $gateway;

    protected array $modules = [];

    protected mixed $external_request;

    protected mixed $external_response;

    protected RouterInterface $router;

    /**
     * The very first request
     */
    protected Request $initial;

    /**
     * The request which response will be returned to router
     */
    protected Request $main;

    protected ?AbstractEndpoint $endpoint = null;

    protected array $request_pool = [];

    protected array $deferred = [];

    protected array $options = [];

    protected bool $is_deferred_stage = false;

    /**
     * Proxy constructor.
     * @throws NotFoundException
     */
    public function __construct(array $options = [])
    {
        $default_options = [
            'debug' => false,
            'fake' => false,
        ];

        $this->options = array_merge($default_options, $options);
    }

    public function setContainer(Container $container): void
    {
        $this->container = $container;
    }

    public function setGateway(GatewayInterface $gateway): void
    {
        $this->gateway = $gateway;
    }

    public function getGateway(): GatewayInterface
    {
        return $this->gateway;
    }

    public function getExternalRequest(): mixed
    {
        return $this->external_request;
    }

    public function getExternalResponse(): mixed
    {
        return $this->external_response;
    }

    public function getModules(): array
    {
        return $this->modules;
    }

    public function setModules(array $modules): void
    {
        $this->modules = $modules;
    }

    public function getRouter(): RouterInterface
    {
        return $this->router;
    }

    public function getRequestPool(): array
    {
        return $this->request_pool;
    }

    public function getInitial(): Request
    {
        return $this->initial;
    }

    public function getMain(): Request
    {
        return $this->main;
    }

    public function initExternalRequestResponse(mixed $external_request = null): void
    {
        if (!$external_request) {
            $external_request = $this->gateway->createRequestFromGlobals();
        }

        $this->external_request = $external_request;
        $this->external_response = $this->gateway->createResponse();

        $this->container->registerSharedService('external_request', $this->external_request);
        $this->container->registerSharedService('external_response', $this->external_response);
    }

    /**
     * @return ConsoleOutput|\Symfony\Component\HttpFoundation\Response|null
     * @throws ForwardException
     * @throws NotFoundException
     * @throws ProxyException
     */
    public function run(bool $return_response = false)
    {
        $module = $this->gateway->dispatch($this->external_request);

        $router_service_name = $this->getModuleComponent($module, 'router');

        if (!$router_service_name) {
            throw new ProxyException("Router for module '$module' is not defined");
        }

        $this->router = $this->container->get($router_service_name);

        [$resource, $action, $args] = $this->router->dispatch($this->external_request);

        $this->initial = $this->main = $this->initializeRequest($module, $resource, $action, $args);

        $response = $this->start();

        if ($return_response === true && $this->external_response instanceof \Symfony\Component\HttpFoundation\Response) {
            $this->external_response->setContent($response->getContent());

            return $this->external_response;
        } else {
            if ($this->isDebug()) {
                $this->runDeferred();

                $this->gateway->sendResponse($this->external_response, $response);
            } else {
                $this->gateway->sendResponse($this->external_response, $response);

                $this->runDeferred();
            }

            return null;
        }
    }

    /**
     * @throws ProxyException
     * @throws NotFoundException
     * @throws ForwardException
     */
    public function execute(string $module, string $resource, string $action, array $args = []): Response
    {
        $request = $this->initializeRequest($module, $resource, $action, $args);

        return $this->executeRequest($request);
    }

    /**
     * @throws ProxyException
     * @throws ForwardException
     * @throws NotFoundException
     */
    public function forward(string $module, string $resource, string $action, array $args = []): void
    {
        if ($this->is_deferred_stage) {
            throw new ProxyException('"Forward" method is not allowed in deferred stage of runtime.');
        }

        $this->main = $this->initializeRequest($module, $resource, $action, $args);

        throw new ForwardException();
    }

    /**
     * @throws ProxyException
     * @throws NotFoundException
     * @throws ForwardException
     */
    public function defer(string $module, string $resource, string $action, array $args = []): void
    {
        if ($this->is_deferred_stage) {
            $this->execute($module, $resource, $action, $args);
        } else {
            $this->deferred[] = new Attributes($module, $resource, $action, $args);
        }
    }

    public function deferCallable(callable $callable): void
    {
        if ($this->is_deferred_stage) {
            $callable();
        } else {
            $this->deferred[] = $callable;
        }
    }

    /**
     * @param string $module_name
     * @param string $key
     * @return mixed
     * @throws ProxyException
     */
    public function getModuleComponent(string $module_name, string $key)
    {
        if (!isset($this->modules[$module_name])) {
            throw new ProxyException("Module \'$module_name\' is not found while getting component \'$key\'");
        }

        /** @var Module $module */
        $module = $this->modules[$module_name];

        switch ($key) {
            case 'container':
                $component = $module->container;
                break;
            case 'router':
                $component = $module->router;
                break;
            case 'request':
                $component = $module->request;
                break;
            case 'response':
                $component = $module->response;
                break;
            default:
                $component = $module->getComponent($key);
                break;
        }

        return $component;
    }

    /**
     * @throws ForwardException
     * @throws NotFoundException
     * @throws ProxyException
     */
    public function pageNotFoundException(): void
    {
        $controller_not_found = $this->router->getNotFoundAttributes();

        $this->forward($controller_not_found[0], $controller_not_found[1], $controller_not_found[2]);
    }

    /**
     * @throws ProxyException
     * @throws NotFoundException
     * @throws ForwardException
     */
    public function runDeferred(): void
    {
        $this->is_deferred_stage = true;

        foreach ($this->deferred as $deferred) {
            if ($deferred instanceof Attributes) {
                $this->execute($deferred->getModule(), $deferred->getResource(), $deferred->getAction(), $deferred->getArgs());
            } elseif (is_callable($deferred)) {
                $deferred();
            }
        }
    }

    public function isDebug(): bool
    {
        return $this->options['debug'] === true;
    }

    public function isFake(): bool
    {
        return $this->options['fake'] === true;
    }

    /**
     * @throws ProxyException
     * @throws NotFoundException
     */
    protected function start(): Response
    {
        try {
            $response = $this->executeRequest($this->main);
        } catch (ForwardException $e) {
            return $this->start();
        }

        return $response;
    }

    /**
     * @throws ProxyException
     * @throws NotFoundException
     */
    protected function initializeRequest(string $module, string $resource, string $action, array $args = []): Request
    {
        $request_service_name = $this->getModuleComponent($module, 'request');

        if (!$request_service_name) {
            throw new ProxyException("Request for module '$module' is not defined");
        }

        return $this->container->get($request_service_name, [$module, $resource, $action, $args]);
    }

    /**
     * @throws ProxyException
     * @throws NotFoundException
     * @throws ForwardException
     */
    protected function executeRequest(Request $request): Response
    {
        $this->request_pool[] = $request;

        if ($request === $this->initial && !in_array($request->getAction(), $this->router->getAllowedActions())) {
            $this->pageNotFoundException();
        }

        if ($request !== $this->main && in_array($request->getAction(), $this->router->getAllowedActions())) {
            throw new ProxyException('Action "' . $request->getAction() . '" is reserved by router for main requests, so can not be used for other requests.');
        }

        $response_service_name = $this->getModuleComponent($request->getModule(), 'response');
        $container_service_name = $this->getModuleComponent($request->getModule(), 'container');

        if (!$response_service_name) {
            throw new ProxyException("Response for module '{$request->getModule()}' is not defined");
        }

        /** @var Module $request_module */
        $request_module = $this->modules[$request->getModule()];

        $inject_response = $this->container->get($response_service_name);
        $inject_container = $container_service_name ? $this->container->get($container_service_name) : $this->container;

        if ($request === $this->main && !$this->endpoint) {
            $this->endpoint = $this->resolveEndpoint($request);
        }

        $fakeResponse = null;

        try {
            $reflection_class = new \ReflectionClass($request->getController());

            $controller = $reflection_class->newInstance(
                $inject_container,
                $request_module->is_container_reachable,
                $this->container->get('application'),
                $this,
                $request,
                $inject_response,
                $request_module->getComponents(),
                $reflection_class,
                $this->endpoint
            );

            if (!method_exists($controller, $request->getAction())) {
                $fakeResponse = $this->controllerOrActionNotFound($request, 'Action "' . $request->getAction() . '" not found in controller "' . $request->getController() . '".');
            }
        } catch (\ReflectionException $e) {
            $fakeResponse = $this->controllerOrActionNotFound($request, 'Controller "' . $request->getController() . '" not found.');
        }

        if ($fakeResponse) {
            $response = new Response();
            $response->setContent($fakeResponse);
        } else {
            /** @var ControllerInterface $controller */
            $response = $controller->_run();

            if (!$response instanceof Response) {
                throw new ProxyException('Method "_run" of controller must return object of type Response.');
            }
        }

        return $response;
    }

    protected function controllerOrActionNotFound(Request $request, string $exceptionMessage): string
    {
        if (
            $this->isFake() &&
            $this->endpoint &&
            $request === $this->main
        ) {
            $fakeResponse = $this->endpoint->fake($request->getAction());
            $fakeResponse['status'] = true;
            return json_encode($fakeResponse, JSON_UNESCAPED_UNICODE);
        }

        if ($request === $this->initial) {
            $this->pageNotFoundException();
        } else {
            throw new ProxyException($exceptionMessage);
        }
    }

    protected function resolveEndpoint(Request $request): ?AbstractEndpoint
    {
        $endpoint = $request->getEndpoint();

        if (!$endpoint) {
            return null;
        }

        if ($this->isDebug()) {
            try {
                /** @var EndpointGenerator $generator */
                $generator = $this->container->get('generator.endpoint');
                $generatedEndpoint = $generator->generate($endpoint);
            } catch (\ReflectionException $e) {
                return null;
            }
        } else {
            $generatedEndpoint = 'Generated\\Endpoint\\' . $endpoint;
        }

        try {
            $reflection_class = new \ReflectionClass($generatedEndpoint);
            $instance = $reflection_class->newInstance();

            if (!method_exists($instance, $request->getAction())) {
                return null;
            }
        } catch (\ReflectionException $e) {
            return null;
        }

        return $instance;
    }
}

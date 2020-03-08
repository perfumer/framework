<?php

namespace Perfumer\Framework\Proxy;

use Perfumer\Component\Container\Container;
use Perfumer\Component\Container\Exception\NotFoundException;
use Perfumer\Framework\Controller\ControllerInterface;
use Perfumer\Framework\Controller\Module;
use Perfumer\Framework\Gateway\GatewayInterface;
use Perfumer\Framework\Router\RouterInterface as Router;
use Perfumer\Framework\Proxy\Exception\ForwardException;
use Perfumer\Framework\Proxy\Exception\ProxyException;

class Proxy
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var GatewayInterface
     */
    protected $gateway;

    /**
     * @var array
     */
    protected $modules = [];

    /**
     * @var mixed
     */
    protected $external_request;

    /**
     * @var mixed
     */
    protected $external_response;

    /**
     * @var Router
     */
    protected $router;

    /**
     * The very first request
     *
     * @var Request
     */
    protected $initial;

    /**
     * The request which response will be returned to router
     *
     * @var Request
     */
    protected $main;

    /**
     * @var array
     */
    protected $request_pool = [];

    /**
     * @var array
     */
    protected $deferred = [];

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var bool
     */
    protected $is_deferred_stage = false;

    /**
     * Proxy constructor.
     * @param Container $container
     * @param array $options
     * @throws NotFoundException
     */
    public function __construct(Container $container, array $options = [])
    {
        $this->container = $container;
        $this->gateway = $container->get('gateway');

        $default_options = [
            'debug' => false
        ];

        $this->options = array_merge($default_options, $options);
    }

    /**
     * @return GatewayInterface
     */
    public function getGateway()
    {
        return $this->gateway;
    }

    /**
     * @return mixed
     */
    public function getExternalRequest()
    {
        return $this->external_request;
    }

    /**
     * @return mixed
     */
    public function getExternalResponse()
    {
        return $this->external_response;
    }

    /**
     * @return array
     */
    public function getModules(): array
    {
        return $this->modules;
    }

    /**
     * @param array $modules
     */
    public function setModules(array $modules): void
    {
        $this->modules = $modules;
    }

    /**
     * @return Router
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * @return array
     */
    public function getRequestPool()
    {
        return $this->request_pool;
    }

    /**
     * @return Request
     */
    public function getInitial()
    {
        return $this->initial;
    }

    /**
     * @return Request
     */
    public function getMain()
    {
        return $this->main;
    }

    /**
     * @param $external_request mixed
     *
     * @throws ProxyException
     * @throws NotFoundException
     * @throws ForwardException
     */
    public function run($external_request = null): void
    {
        if (!$external_request) {
            $external_request = $this->gateway->createRequestFromGlobals();
        }

        $this->external_request = $external_request;
        $this->external_response = $this->gateway->createResponse();

        $this->container->registerSharedService('external_request', $this->external_request);
        $this->container->registerSharedService('external_response', $this->external_response);

        $module = $this->gateway->dispatch($this->external_request);

        $router_service_name = $this->getModuleComponent($module, 'router');

        if (!$router_service_name) {
            throw new ProxyException("Router for module '$module' is not defined");
        }

        $this->router = $this->container->get($router_service_name);

        list($resource, $action, $args) = $this->router->dispatch($this->external_request);

        $this->initial = $this->main = $this->initializeRequest($module, $resource, $action, $args);

        $response = $this->start();

        if ($this->options['debug'] === true) {
            $this->runDeferred();

            $this->gateway->sendResponse($this->external_response, $response);
        } else {
            $this->gateway->sendResponse($this->external_response, $response);

            $this->runDeferred();
        }
    }

    /**
     * @param string $module
     * @param string $resource
     * @param string $action
     * @param array $args
     * @return Response
     * @throws ProxyException
     * @throws NotFoundException
     * @throws ForwardException
     */
    public function execute($module, $resource, $action, array $args = [])
    {
        $request = $this->initializeRequest($module, $resource, $action, $args);

        return $this->executeRequest($request);
    }

    /**
     * @param string $module
     * @param string $resource
     * @param string $action
     * @param array $args
     * @throws ProxyException
     * @throws ForwardException
     * @throws NotFoundException
     */
    public function forward($module, $resource, $action, array $args = [])
    {
        if ($this->is_deferred_stage) {
            throw new ProxyException('"Forward" method is not allowed in deferred stage of runtime.');
        }

        $this->main = $this->initializeRequest($module, $resource, $action, $args);

        throw new ForwardException();
    }

    /**
     * @param string $module
     * @param string $resource
     * @param string $action
     * @param array $args
     * @throws ProxyException
     * @throws NotFoundException
     * @throws ForwardException
     */
    public function defer($module, $resource, $action, array $args = [])
    {
        if ($this->is_deferred_stage) {
            $this->execute($module, $resource, $action, $args);
        } else {
            $this->deferred[] = new Attributes($module, $resource, $action, $args);
        }
    }

    /**
     * @param callable $callable
     */
    public function deferCallable(callable $callable)
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
     * @return string|null
     * @throws ProxyException
     */
    public function getModuleComponent(string $module_name, string $key): ?string
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
    public function pageNotFoundException()
    {
        $controller_not_found = $this->router->getNotFoundAttributes();

        $this->forward($controller_not_found[0], $controller_not_found[1], $controller_not_found[2]);
    }

    /**
     * @return Response
     * @throws ProxyException
     * @throws NotFoundException
     */
    protected function start()
    {
        try {
            $response = $this->executeRequest($this->main);
        } catch (ForwardException $e) {
            return $this->start();
        }

        return $response;
    }

    /**
     * @param string $module
     * @param string $resource
     * @param string $action
     * @param array $args
     * @return Request
     * @throws ProxyException
     * @throws NotFoundException
     */
    protected function initializeRequest($module, $resource, $action, array $args = []): Request
    {
        $request_service_name = $this->getModuleComponent($module, 'request');

        if (!$request_service_name) {
            throw new ProxyException("Request for module '$module' is not defined");
        }

        return $this->container->get($request_service_name, [$module, $resource, $action, $args]);
    }

    /**
     * @param Request $request
     * @return Response
     * @throws ProxyException
     * @throws NotFoundException
     * @throws ForwardException
     */
    protected function executeRequest(Request $request)
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
                $reflection_class
            );

            if (!method_exists($controller, $request->getAction())) {
                if ($request === $this->initial) {
                    $this->pageNotFoundException();
                } else {
                    throw new ProxyException('Action "' . $request->getAction() . '" not found in controller "' . $request->getController() . '".');
                }
            }
        } catch (\ReflectionException $e) {
            if ($request === $this->initial) {
                $this->pageNotFoundException();
            } else {
                throw new ProxyException('Controller "' . $request->getController() . '" not found.');
            }
        }

        /** @var ControllerInterface $controller */

        $response = $controller->_run();

        if (!$response instanceof Response) {
            throw new ProxyException('Method "_run" of controller must return object of type Response.');
        }

        return $response;
    }

    /**
     * @throws ProxyException
     * @throws NotFoundException
     * @throws ForwardException
     */
    protected function runDeferred()
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
}

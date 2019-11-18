<?php

namespace Perfumer\Framework\Proxy;

use Perfumer\Component\Container\Container;
use Perfumer\Framework\Controller\ControllerInterface;
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

    public function run()
    {
        $bundle = $this->gateway->dispatch();

        $router_service_name = $this->container->resolveBundleAlias($bundle, 'router');

        $this->router = $this->container->get($router_service_name);

        list($resource, $action, $args) = $this->router->dispatch();

        $this->initial = $this->main = $this->initializeRequest($bundle, $resource, $action, $args);

        $response = $this->start();

        if ($this->options['debug'] === true) {
            $this->runDeferred();

            $this->router->sendResponse($response->getContent());
        } else {
            $this->router->sendResponse($response->getContent());

            $this->runDeferred();
        }
    }

    /**
     * @param string $bundle
     * @param string $resource
     * @param string $action
     * @param array $args
     * @return Response
     */
    public function execute($bundle, $resource, $action, array $args = [])
    {
        $request = $this->initializeRequest($bundle, $resource, $action, $args);

        return $this->executeRequest($request);
    }

    /**
     * @param string $bundle
     * @param string $resource
     * @param string $action
     * @param array $args
     * @throws ProxyException
     * @throws ForwardException
     */
    public function forward($bundle, $resource, $action, array $args = [])
    {
        if ($this->is_deferred_stage) {
            throw new ProxyException('"Forward" method is not allowed in deferred stage of runtime.');
        }

        $this->main = $this->initializeRequest($bundle, $resource, $action, $args);

        throw new ForwardException();
    }

    /**
     * @param string $bundle
     * @param string $resource
     * @param string $action
     * @param array $args
     */
    public function defer($bundle, $resource, $action, array $args = [])
    {
        if ($this->is_deferred_stage) {
            $this->execute($bundle, $resource, $action, $args);
        } else {
            $this->deferred[] = new Attributes($bundle, $resource, $action, $args);
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

    public function pageNotFoundException()
    {
        $controller_not_found = $this->router->getNotFoundAttributes();

        $this->forward($controller_not_found[0], $controller_not_found[1], $controller_not_found[2]);
    }

    /**
     * @return Response
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
     * @param string $bundle
     * @param string $resource
     * @param string $action
     * @param array $args
     * @return Request
     */
    protected function initializeRequest($bundle, $resource, $action, array $args = [])
    {
        $request_service_name = $this->container->resolveBundleAlias($bundle, 'request');

        return $this->container->get($request_service_name, [$bundle, $resource, $action, $args]);
    }

    /**
     * @param Request $request
     * @return Response
     * @throws ProxyException
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

        $definition = $request->getBundle() . '.' . $request->getResource();

        if ($this->container->has($definition)) {
            $controller = $this->container->get($definition);
        } else {
            try {
                $reflection_class = new \ReflectionClass($request->getController());

                $controller = $reflection_class->newInstance($this->container, $request, $reflection_class);

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
        }

        /** @var ControllerInterface $controller */

        $response = $controller->_run();

        if (!$response instanceof Response) {
            throw new ProxyException('Method "_run" of controller must return object of type Response.');
        }

        return $response;
    }

    protected function runDeferred()
    {
        $this->is_deferred_stage = true;

        foreach ($this->deferred as $deferred) {
            if ($deferred instanceof Attributes) {
                $this->execute($deferred->getBundle(), $deferred->getResource(), $deferred->getAction(), $deferred->getArgs());
            } elseif (is_callable($deferred)) {
                $deferred();
            }
        }
    }
}

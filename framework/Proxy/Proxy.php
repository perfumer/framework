<?php

namespace Perfumer\Framework\Proxy;

use Perfumer\Component\Container\Container;
use Perfumer\Framework\Controller\ControllerInterface;
use Perfumer\Framework\Bundle\Bundler;
use Perfumer\Framework\Bundle\Resolver\ResolverInterface as BundleResolver;
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
     * @var Bundler
     */
    protected $bundler;

    /**
     * @var BundleResolver
     */
    protected $bundle_resolver;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var Request
     */
    protected $current_initial;

    /**
     * @var Request
     */
    protected $next;

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
        $this->bundler = $container->get('bundler');
        $this->bundle_resolver = $container->get('bundle_resolver');

        $default_options = [
            'debug' => false
        ];

        $this->options = array_merge($default_options, $options);
    }

    /**
     * @return BundleResolver
     */
    public function getBundleResolver()
    {
        return $this->bundle_resolver;
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
    public function getMain()
    {
        return $this->request_pool[0];
    }

    public function run()
    {
        $bundle = $this->bundle_resolver->dispatch();

        $this->router = $this->bundler->getService($bundle, 'router');

        list($resource, $action, $args) = $this->router->dispatch();

        $this->next = $this->initializeRequest($bundle, $resource, $action, $args);

        $response = $this->start();

        if ($this->options['debug'] === true) {
            $this->runDeferred();

            $this->router->sendResponse($response);
        } else {
            $this->router->sendResponse($response);

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

        $this->current_initial = null;

        $this->next = $this->initializeRequest($bundle, $resource, $action, $args);

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
            $this->deferred[] = [$bundle, $resource, $action, $args];
        }
    }

    /**
     * @param string $event_name
     * @param Event $event
     */
    public function trigger($event_name, Event $event)
    {
        if ($subscribers = $this->bundler->getAsyncSubscribers($event_name)) {
            foreach ($subscribers as $subscriber) {
                $this->defer($subscriber[0], $subscriber[1], $subscriber[2], [$event]);
            }
        }

        if ($subscribers = $this->bundler->getSyncSubscribers($event_name)) {
            foreach ($subscribers as $subscriber) {
                $this->execute($subscriber[0], $subscriber[1], $subscriber[2], [$event]);
            }
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
            $response = $this->executeRequest($this->next);
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
        list($bundle, $resource, $action) = $this->bundler->overrideController($bundle, $resource, $action);

        return $this->bundler->getService($bundle, 'request', [$bundle, $resource, $action, $args]);
    }

    /**
     * @param Request $request
     * @return Response
     * @throws ProxyException
     */
    protected function executeRequest(Request $request)
    {
        if (count($this->request_pool) != 0) {
            $request->setMain($this->getMain());
        }

        $this->request_pool[] = $request;

        if ($this->current_initial === null) {
            $this->current_initial = $request;
        } else {
            $request->setInitial($this->current_initial);
        }

        if ($request->isMain() && !in_array($request->getAction(), $this->router->getAllowedActions())) {
            $this->pageNotFoundException();
        }

        if (!$request->isMain() && in_array($request->getAction(), $this->router->getAllowedActions())) {
            throw new ProxyException('Action "' . $request->getAction() . '" is reserved by router for external requests, so can not be used for internal requests.');
        }

        $controller_class = $request->getController();

        if ($this->container->has($controller_class)) {
            $controller = $this->container->get($controller_class);
        } else {
            try {
                $reflection_class = new \ReflectionClass($request->getController());

                $controller = $reflection_class->newInstance($this->container, $request, $reflection_class);

                if (!method_exists($controller, $request->getAction())) {
                    if ($request->isMain()) {
                        $this->pageNotFoundException();
                    } else {
                        throw new ProxyException('Action "' . $request->getAction() . '" not found in controller "' . $controller_class . '".');
                    }
                }
            } catch (\ReflectionException $e) {
                $this->pageNotFoundException();
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

        foreach ($this->deferred as $job) {
            $this->execute($job[0], $job[1], $job[2], $job[3]);
        }
    }
}

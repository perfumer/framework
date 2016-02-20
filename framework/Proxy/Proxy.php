<?php

namespace Perfumer\Framework\Proxy;

use Perfumer\Component\Container\Container;
use Perfumer\Framework\Controller\ControllerInterface;
use Perfumer\Framework\Bundle\Bundler;
use Perfumer\Framework\BundleRouter\RouterInterface as BundleRouter;
use Perfumer\Framework\ExternalRouter\RouterInterface as ExternalRouter;
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
     * @var BundleRouter
     */
    protected $bundle_router;

    /**
     * @var ExternalRouter
     */
    protected $external_router;

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
     * Proxy constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->bundler = $container->get('bundler');
        $this->bundle_router = $container->get('bundle_router');
    }

    /**
     * @return BundleRouter
     */
    public function getBundleRouter()
    {
        return $this->bundle_router;
    }

    /**
     * @return ExternalRouter
     */
    public function getExternalRouter()
    {
        return $this->external_router;
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
        $bundle = $this->bundle_router->dispatch();

        $this->external_router = $this->bundler->getService($bundle, 'external_router');

        list($url, $action, $args) = $this->external_router->dispatch();

        $this->next = $this->initializeRequest($bundle, $url, $action, $args);

        $response = $this->start();

        $this->external_router->sendResponse($response);

        foreach ($this->deferred as $job) {
            $this->execute($job[0], $job[1], $job[2], $job[3]);
        }
    }

    /**
     * @param string $bundle
     * @param string $url
     * @param string $action
     * @param array $args
     * @return Response
     */
    public function execute($bundle, $url, $action, array $args = [])
    {
        $request = $this->initializeRequest($bundle, $url, $action, $args);

        return $this->executeRequest($request);
    }

    /**
     * @param string $bundle
     * @param string $url
     * @param string $action
     * @param array $args
     * @throws ForwardException
     */
    public function forward($bundle, $url, $action, array $args = [])
    {
        $this->current_initial = null;

        $this->next = $this->initializeRequest($bundle, $url, $action, $args);

        throw new ForwardException();
    }

    /**
     * @param string $bundle
     * @param string $url
     * @param string $action
     * @param array $args
     * @return $this
     */
    public function defer($bundle, $url, $action, array $args = [])
    {
        $this->deferred[] = [$bundle, $url, $action, $args];

        return $this;
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
     * @param string $url
     * @param string $action
     * @param array $args
     * @return Request
     */
    protected function initializeRequest($bundle, $url, $action, array $args = [])
    {
        list($bundle, $url, $action) = $this->bundler->overrideController($bundle, $url, $action);

        return $this->bundler->getService($bundle, 'internal_router')->dispatch($url)->setBundle($bundle)->setAction($action)->setArgs($args);
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

        $controller_class = $request->getController();

        if ($this->container->has($controller_class)) {
            $controller = $this->container->get($controller_class);
        } else {
            try {
                $reflection_class = new \ReflectionClass($request->getController());

                $controller = $reflection_class->newInstance($this->container, $request, $reflection_class);
            } catch (\ReflectionException $e) {
                $controller_not_found = $this->external_router->getControllerNotFound();

                $this->forward($controller_not_found[0], $controller_not_found[1], $controller_not_found[2]);
            }

        }

        /** @var ControllerInterface $controller */

        $response = $controller->_run();

        if (!$response instanceof Response) {
            throw new ProxyException('Method "_run" of controller must return object of type Response.');
        }

        return $response;
    }
}

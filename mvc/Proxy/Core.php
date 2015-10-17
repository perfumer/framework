<?php

namespace Perfumer\MVC\Proxy;

use \Perfumer\Component\Container\Core as Container;
use Perfumer\MVC\Bundler\Bundler;
use Perfumer\MVC\ExternalRouter\RouterInterface as ExternalRouter;
use Perfumer\MVC\InternalRouter\RouterInterface as InternalRouter;
use Perfumer\MVC\Proxy\Exception\ForwardException;
use Symfony\Component\HttpFoundation\Response;

class Core
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
    protected $background_jobs = [];

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->bundler = $container->getService('bundler');
        $this->external_router = $container->getService('external_router');
    }

    /**
     * @return ExternalRouter
     */
    public function getExternalRouter()
    {
        return $this->external_router;
    }

    public function getRequestPool()
    {
        return $this->request_pool;
    }

    public function getMain()
    {
        return $this->request_pool[0];
    }

    public function run()
    {
        list($bundle, $url, $action, $args) = $this->external_router->dispatch();

        $this->next = $this->initializeRequest($bundle, $url, $action, $args);

        $this->start()->send();

        foreach ($this->background_jobs as $job)
            $this->execute($job[0], $job[1], $job[2], $job[3], $job[4]);
    }

    public function execute($bundle, $url, $action, array $args = [], array $context = [])
    {
        $request = $this->initializeRequest($bundle, $url, $action, $args, $context);

        return $this->executeRequest($request);
    }

    public function forward($bundle, $url, $action, array $args = [], array $context = [])
    {
        $this->current_initial = null;

        $this->next = $this->initializeRequest($bundle, $url, $action, $args, $context);

        throw new ForwardException();
    }

    public function addBackgroundJob($bundle, $url, $action, array $args = [], array $context = [])
    {
        $this->background_jobs[] = [$bundle, $url, $action, $args, $context];

        return $this;
    }

    /**
     * @return Response
     */
    protected function start()
    {
        try
        {
            $response = $this->executeRequest($this->next);
        }
        catch (ForwardException $e)
        {
            return $this->start();
        }

        return $response;
    }

    /**
     * @return Request
     */
    protected function initializeRequest($bundle, $url, $action, array $args = [], array $context = [])
    {
        $context_bundle = isset($context['bundle']) ? $context['bundle'] : null;

        list($bundle, $url, $action) = $this->bundler->overrideController($bundle, $url, $action, $context_bundle);

        return $this->bundler->getService($bundle, 'internal_router')->dispatch($url)->setBundle($bundle)->setAction($action)->setArgs($args);
    }

    protected function executeRequest(Request $request)
    {
        if (count($this->request_pool) != 0)
            $request->setMain($this->getMain());

        $this->request_pool[] = $request;

        if ($this->current_initial === null)
        {
            $this->current_initial = $request;
        }
        else
        {
            $request->setInitial($this->current_initial);
        }

        try
        {
            $reflection_class = new \ReflectionClass($request->getController());
        }
        catch (\ReflectionException $e)
        {
            $this->forward('framework', 'exception/html', 'controllerNotFound', [], ['bundle' => $request->getBundle()]);
        }

        $response = new Response;

        $controller = $reflection_class->newInstance($this->container, $request, $response, $reflection_class);

        return $reflection_class->getMethod('process')->invoke($controller);
    }
}
<?php

namespace Perfumer\MVC\Proxy;

use Perfumer\MVC\ExternalRouter\RouterInterface as ExternalRouter;
use Perfumer\MVC\InternalRouter\RouterInterface as InternalRouter;
use Perfumer\MVC\Proxy\Exception\ForwardException;
use Perfumer\MVC\View\ViewFactory;
use Symfony\Component\HttpFoundation\Response;

class Core
{
    /**
     * @var ExternalRouter
     */
    protected $external_router;

    /**
     * @var InternalRouter
     */
    protected $internal_router;

    /**
     * @var ViewFactory
     */
    protected $view_factory;

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
     *
     * Array of variables to inject to controller
     */
    protected $injected = [];

    /**
     * @var array
     */
    protected $request_pool = [];

    public function __construct(ExternalRouter $external_router, InternalRouter $internal_router, ViewFactory $view_factory)
    {
        $this->external_router = $external_router;
        $this->internal_router = $internal_router;
        $this->view_factory = $view_factory;
    }

    /**
     * @return ExternalRouter
     */
    public function getExternalRouter()
    {
        return $this->external_router;
    }

    /**
     * @return InternalRouter
     */
    public function getInternalRouter()
    {
        return $this->internal_router;
    }

    /**
     * @return ViewFactory
     */
    public function getViewFactory()
    {
        return $this->view_factory;
    }

    public function getInjected($key = null)
    {
        if ($key === null)
            return $this->injected;

        return isset($this->injected[$key]) ? $this->injected[$key] : null;
    }

    public function inject($key, $value)
    {
        $this->injected[$key] = $value;

        return $this;
    }

    public function injectArray($values)
    {
        $this->injected = array_merge($this->injected, $values);

        return $this;
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
        list($url, $action, $args) = $this->external_router->dispatch();

        $this->next = $this->internal_router->dispatch($url, $action, $args);

        $this->start()->send();
    }

    public function execute($url, $action, array $args = [])
    {
        $request = $this->internal_router->dispatch($url, $action, $args);

        return $this->executeController($request);
    }

    public function forward($url, $action, array $args = [])
    {
        $this->current_initial = null;

        $this->next = $this->internal_router->dispatch($url, $action, $args);

        throw new ForwardException();
    }

    /**
     * @return Response
     */
    protected function start()
    {
        try
        {
            $response = $this->executeController($this->next);
        }
        catch (ForwardException $e)
        {
            return $this->start();
        }

        return $response;
    }

    protected function executeController(Request $request)
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
            $this->forward('exception/page', 'controllerNotFound');
        }

        $response = new Response;

        $controller = $reflection_class->newInstance($this, $request, $response, $reflection_class);

        return $reflection_class->getMethod('execute')->invoke($controller);
    }
}
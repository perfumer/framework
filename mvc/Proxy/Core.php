<?php

namespace Perfumer\MVC\Proxy;

use Perfumer\Component\Container\Core as Container;
use Perfumer\MVC\Proxy\Exception\ForwardException;

class Core
{
    /**
     * @var Container
     */
    protected $container;

    protected $request_pool = [];

    /**
     * @var Request
     */
    protected $current_initial;

    /**
     * @var Request
     */
    protected $next;

    protected $external_router;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->external_router = $container->getService('external_router');
    }

    public function run()
    {
        list($url, $action, $args) = $this->external_router->dispatch();

        $this->next = $this->container->getService('request')->init($url, $action, $args);

        $this->start()->send();
    }

    public function execute($url, $action, array $args = [])
    {
        $request = $this->container->getService('request')->init($url, $action, $args);

        return $this->executeController($request);
    }

    public function forward($url, $action, array $args = [])
    {
        $this->current_initial = null;

        $this->next = $this->container->getService('request')->init($url, $action, $args);

        throw new ForwardException();
    }

    public function getRequestPool()
    {
        return $this->request_pool;
    }

    public function getMain()
    {
        return $this->request_pool[0];
    }

    public function generateUrl($url, $id = null, $query = [], $prefixes = [])
    {
        return $this->external_router->generateUrl($url, $id, $query, $prefixes);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
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

        $response = $this->container->getService('response');

        $controller = $reflection_class->newInstance($this->container, $request, $response, $reflection_class);

        return $reflection_class->getMethod('execute')->invoke($controller);
    }

    public function getPrefix($name = null, $default = null)
    {
        return $this->external_router->getPrefix($name, $default);
    }

    public function setPrefix($name, $value)
    {
        $this->external_router->setPrefix($name, $value);

        return $this;
    }

    public function getId($index = null)
    {
        return $this->external_router->getId($index);
    }

    public function setId($id, $index = null)
    {
        $this->external_router->setId($id, $index);

        return $this;
    }

    public function getArg($name = null, $default = null)
    {
        return $this->external_router->getArg($name, $default);
    }

    public function hasArgs()
    {
        return $this->external_router->hasArgs();
    }

    public function setArg($name, $value)
    {
        $this->external_router->setArg($name, $value);

        return $this;
    }

    public function setArgsArray($array)
    {
        $this->external_router->setArgsArray($array);

        return $this;
    }

    public function addArgsArray($array)
    {
        $this->external_router->addArgsArray($array);

        return $this;
    }

    public function deleteArgs(array $keys = [])
    {
        $this->external_router->deleteArgs($keys);

        return $this;
    }

    public function getQuery($name = null, $default = null)
    {
        return $this->external_router->getQuery($name, $default);
    }

    public function hasQuery()
    {
        return $this->external_router->hasQuery();
    }

    public function setQuery($name, $value)
    {
        $this->external_router->setQuery($name, $value);

        return $this;
    }

    public function setQueryArray($array)
    {
        $this->external_router->setQueryArray($array);

        return $this;
    }

    public function addQueryArray($array)
    {
        $this->external_router->addQueryArray($array);

        return $this;
    }

    public function deleteQuery(array $keys = [])
    {
        $this->external_router->deleteQuery($keys);

        return $this;
    }
}
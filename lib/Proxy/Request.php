<?php

namespace Perfumer\Proxy;

use Perfumer\Container\Core as Container;

class Request
{
    protected $container;

    protected $url;
    protected $args;
    protected $controller;
    protected $action;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function execute($url, $action, array $args = [])
    {
        $url = trim($url, '/');
        $path = explode('/', $url);

        $this->url = $url;
        $this->args = $args;
        $this->action = $action;
        $this->controller = 'App\\Controller\\' . implode('\\', array_map('ucfirst', $path)) . 'Controller';

        try
        {
            $reflection_class = new \ReflectionClass($this->getController());
        }
        catch (\ReflectionException $e)
        {
            $this->container->s('proxy')->forward('exception/html', 'controllerNotFound');
        }

        $request = $this;
        $response = $this->container->s('response');
        $controller = $reflection_class->newInstance($this->container, $request, $response);

        return $reflection_class->getMethod('execute')->invoke($controller);
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getArgs()
    {
        return $this->args;
    }

    public function getController()
    {
        return $this->controller;
    }

    public function getAction()
    {
        return $this->action;
    }
}
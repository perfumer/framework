<?php

namespace Perfumer;

use Perfumer\Container\Core as Container;
use Perfumer\Controller\HTTPException;

class Request
{
    protected $container;

    public $url;
    public $controller;
    public $method;
    public $template;
    public $css;
    public $js;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function execute($url = null, $method = null, array $args = [])
    {
        if ($url === null)
        {
            $url = ($_SERVER['PATH_INFO'] !== '/') ? $_SERVER['PATH_INFO'] : $this->container->p('app.default_url');
        }

        $url = trim($url, '/');
        $path = explode('/', $url);

        $this->url = $url;
        $this->method = ($method === null) ? strtolower($_SERVER['REQUEST_METHOD']) : $method;
        $this->template = $url . '/' . $this->method . '.twig';
        $this->css = $url . '/' . $this->method . '.css';
        $this->js = $url . '/' . $this->method . '.js';
        $this->controller = 'App\\Controller\\' . implode('\\', array_map('ucfirst', $path)) . 'Controller';

        try
        {
            $reflection_class = new \ReflectionClass($this->controller);
        }
        catch (\ReflectionException $e)
        {
            throw new HTTPException("Controller '{$this->controller}' does not exist", 404);
        }

        $request = $this;
        $response = $this->container->s('response');
        $controller = $reflection_class->newInstance($this->container, $request, $response);

        return $reflection_class->getMethod('execute')->invoke($controller, $this->method, $args);
    }
}
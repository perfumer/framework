<?php

namespace Perfumer;

use Perfumer\Container\Core as Container;
use Perfumer\Controller\Exception\HTTPException;

class Request
{
    protected $container;

    protected $url;
    protected $controller;
    protected $method;
    protected $template;
    protected $css;
    protected $js;

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

        $this->setURL($url);
        $this->setMethod(($method === null) ? strtolower($_SERVER['REQUEST_METHOD']) : $method);
        $this->setTemplate($url . '/' . $this->method . '.twig');
        $this->setCSS($url . '/' . $this->method . '.css');
        $this->setJS($url . '/' . $this->method . '.js');
        $this->setController('App\\Controller\\' . implode('\\', array_map('ucfirst', $path)) . 'Controller');

        try
        {
            $reflection_class = new \ReflectionClass($this->getController());
        }
        catch (\ReflectionException $e)
        {
            throw new HTTPException("Controller '{$this->getController()}' does not exist", 404);
        }

        $request = $this;
        $response = $this->container->s('response');
        $controller = $reflection_class->newInstance($this->container, $request, $response);

        return $reflection_class->getMethod('execute')->invoke($controller, $this->getMethod(), $args);
    }

    public function getURL()
    {
        return $this->url;
    }

    public function setURL($url)
    {
        $this->url = $url;
        return $this;
    }

    public function getController()
    {
        return $this->controller;
    }

    public function setController($controller)
    {
        $this->controller = $controller;
        return $this;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }

    public function getCSS()
    {
        return $this->css;
    }

    public function setCSS($css)
    {
        $this->css = $css;
        return $this;
    }

    public function getJS()
    {
        return $this->js;
    }

    public function setJS($js)
    {
        $this->js = $js;
        return $this;
    }
}
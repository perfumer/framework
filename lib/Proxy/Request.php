<?php

namespace Perfumer\Proxy;

use Perfumer\Container\Core as Container;
use Perfumer\Proxy\Core as Proxy;

class Request
{
    protected $container;

    protected $url;
    protected $args;
    protected $controller;
    protected $action;
    protected $template;
    protected $css;
    protected $js;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function execute($url, $action, array $args = [])
    {
        $url = trim($url, '/');
        $path = explode('/', $url);

        $this->setURL($url);
        $this->setArgs($args);
        $this->setAction($action);
        $this->setTemplate($url . '/' . $this->getAction() . '.twig');
        $this->setCSS($url . '/' . $this->getAction() . '.css');
        $this->setJS($url . '/' . $this->getAction() . '.js');
        $this->setController('App\\Controller\\' . implode('\\', array_map('ucfirst', $path)) . 'Controller');

        try
        {
            $reflection_class = new \ReflectionClass($this->getController());
        }
        catch (\ReflectionException $e)
        {
            $this->container->s('proxy')->forward('exception/html', 'pageNotFound');
        }

        $request = $this;
        $response = $this->container->s('response');
        $controller = $reflection_class->newInstance($this->container, $request, $response);

        return $reflection_class->getMethod('execute')->invoke($controller);
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

    public function getArgs()
    {
        return $this->args;
    }

    public function setArgs($args)
    {
        $this->args = $args;
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

    public function getAction()
    {
        return $this->action;
    }

    public function setAction($action)
    {
        $this->action = $action;
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
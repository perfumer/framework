<?php

namespace Perfumer;

use Perfumer\Container\Core as Container;
use Perfumer\Controller\Exception\HTTPException;

class Request
{
    protected $container;

    protected $url;
    protected $controller;
    protected $action;
    protected $template;
    protected $css;
    protected $js;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function execute($url = null, $action = null, array $args = [])
    {
        if ($url === null)
        {
            $url = ($_SERVER['PATH_INFO'] !== '/') ? $_SERVER['PATH_INFO'] : $this->container->p('url.default');
        }

        $url = trim($url, '/');
        $path = explode('/', $url);

        $this->setURL($url);
        $this->setAction(($action === null) ? strtolower($_SERVER['REQUEST_METHOD']) : $action);
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
            throw new HTTPException("Controller '{$this->getController()}' does not exist", 404);
        }

        $request = $this;
        $response = $this->container->s('response');
        $controller = $reflection_class->newInstance($this->container, $request, $response);

        return $reflection_class->getMethod('execute')->invoke($controller, $args);
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
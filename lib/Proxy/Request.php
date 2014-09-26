<?php

namespace Perfumer\Proxy;

class Request
{
    /**
     * @var Request
     */
    protected $initial;

    protected $url;
    protected $args;
    protected $controller;
    protected $action;

    public function init($url, $action, array $args = [])
    {
        $path = explode('/', $url);

        $this->url = $url;
        $this->args = $args;
        $this->action = $action;
        $this->controller = 'App\\Controller\\' . implode('\\', array_map('ucfirst', $path)) . 'Controller';

        return $this;
    }

    public function setInitial(Request $request)
    {
        $this->initial = $request;

        return $this;
    }

    public function getInitial()
    {
        return $this->initial;
    }

    public function isInitial()
    {
        return $this->initial !== null;
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
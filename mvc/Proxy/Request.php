<?php

namespace Perfumer\MVC\Proxy;

class Request
{
    /**
     * @var Request
     */
    protected $main;

    /**
     * @var Request
     */
    protected $initial;

    protected $url;
    protected $controller;
    protected $action;
    protected $args;

    public function setMain(Request $request)
    {
        $this->main = $request;

        return $this;
    }

    public function getMain()
    {
        return $this->main;
    }

    public function isMain()
    {
        return $this->main === null;
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
        return $this->initial === null;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
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

    public function getArgs()
    {
        return $this->args;
    }

    public function setArgs(array $args)
    {
        $this->args = $args;

        return $this;
    }
}
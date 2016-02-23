<?php

namespace Perfumer\Framework\Proxy;

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

    /**
     * @var string
     */
    protected $bundle;

    /**
     * @var string
     */
    protected $resource;

    /**
     * @var string
     */
    protected $action;

    /**
     * @var array
     */
    protected $args;

    /**
     * @var string
     */
    protected $controller;

    /**
     * @param Request $request
     * @return $this
     */
    public function setMain(Request $request)
    {
        $this->main = $request;

        return $this;
    }

    /**
     * @return Request
     */
    public function getMain()
    {
        return $this->main;
    }

    /**
     * @return bool
     */
    public function isMain()
    {
        return $this->main === null;
    }

    /**
     * @param Request $request
     * @return $this
     */
    public function setInitial(Request $request)
    {
        $this->initial = $request;

        return $this;
    }

    /**
     * @return Request
     */
    public function getInitial()
    {
        return $this->initial;
    }

    /**
     * @return bool
     */
    public function isInitial()
    {
        return $this->initial === null;
    }

    /**
     * @return string
     */
    public function getIdentity()
    {
        return $this->bundle . '.' . $this->resource . '.' . $this->action;
    }

    /**
     * @return string
     */
    public function getBundle()
    {
        return $this->bundle;
    }

    /**
     * @param string $bundle
     * @return $this
     */
    public function setBundle($bundle)
    {
        $this->bundle = $bundle;

        return $this;
    }

    /**
     * @return string
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @param string $resource
     * @return $this
     */
    public function setResource($resource)
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @param string $controller
     * @return $this
     */
    public function setController($controller)
    {
        $this->controller = $controller;

        return $this;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param string $action
     * @return $this
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * @return array
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * @param array $args
     * @return $this
     */
    public function setArgs(array $args)
    {
        $this->args = $args;

        return $this;
    }
}

<?php

namespace Perfumer\Framework\Proxy;

class Attributes
{
    /**
     * @var string
     */
    protected $module;

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
     * @param string $module
     * @param string $resource
     * @param string $action
     * @param array $args
     */
    public function __construct($module, $resource, $action, $args = [])
    {
        $this->module = (string) $module;
        $this->resource = (string) $resource;
        $this->action = (string) $action;
        $this->args = (array) $args;
    }

    /**
     * @return string
     */
    public function getIdentity()
    {
        return $this->module . '.' . $this->resource . '.' . $this->action;
    }

    /**
     * @return string
     * @deprecated Use getModule() instead
     */
    public function getBundle()
    {
        return $this->module;
    }

    /**
     * @return string
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * @return string
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return array
     */
    public function getArgs()
    {
        return $this->args;
    }
}

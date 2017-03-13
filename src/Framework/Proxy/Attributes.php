<?php

namespace Perfumer\Framework\Proxy;

class Attributes
{
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
     * @param string $bundle
     * @param string $resource
     * @param string $action
     * @param array $args
     */
    public function __construct($bundle, $resource, $action, $args = [])
    {
        $this->bundle = (string) $bundle;
        $this->resource = (string) $resource;
        $this->action = (string) $action;
        $this->args = (array) $args;
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

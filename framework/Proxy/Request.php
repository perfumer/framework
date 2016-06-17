<?php

namespace Perfumer\Framework\Proxy;

class Request
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
     * @var array
     */
    protected $options = [];

    public function __construct($bundle, $resource, $action, $args = [], $options = [])
    {
        $this->bundle = (string) $bundle;
        $this->resource = (string) $resource;
        $this->action = (string) $action;
        $this->args = (array) $args;

        $default_options = [
            'prefix' => 'App\\Controller',
            'suffix' => 'Controller'
        ];

        $this->options = array_merge($default_options, (array) $options);
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
    public function getController()
    {
        $path = str_replace('-', '', ucwords($this->resource, '-_/'));
        $path = str_replace('/', '\\', $path);

        return $this->options['prefix'] . '\\' . $path . $this->options['suffix'];
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

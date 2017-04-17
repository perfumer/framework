<?php

namespace Perfumer\Framework\Proxy;

class Request extends Attributes
{
    /**
     * @var array
     */
    protected $options = [];

    /**
     * @param string $bundle
     * @param string $resource
     * @param string $action
     * @param array $args
     * @param array $options
     */
    public function __construct($bundle, $resource, $action, $args = [], $options = [])
    {
        parent::__construct($bundle, $resource, $action, $args);

        $default_options = [
            'prefix' => 'App\\Controller',
            'suffix' => 'Controller'
        ];

        $this->options = array_merge($default_options, (array) $options);
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
}

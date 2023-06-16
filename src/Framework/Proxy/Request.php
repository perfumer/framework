<?php

namespace Perfumer\Framework\Proxy;

class Request extends Attributes
{
    protected array $options = [];

    public function __construct(string $module, string $resource, string $action, array $args = [], array $options = [])
    {
        parent::__construct($module, $resource, $action, $args);

        $default_options = [
            'prefix' => 'App\\Controller',
            'suffix' => 'Controller',
            'endpoint_enabled' => false,
            'endpoint_prefix' => 'App\\Endpoint',
            'endpoint_suffix' => 'Endpoint',
        ];

        $this->options = array_merge($default_options, (array) $options);
    }

    public function getController(): ?string
    {
        $path = str_replace('-', '', ucwords($this->resource, '-_/'));
        $path = str_replace('/', '\\', $path);

        return $this->options['prefix'] . '\\' . $path . $this->options['suffix'];
    }

    public function getEndpoint(): ?string
    {
        if (!$this->options['endpoint_enabled']) {
            return null;
        }

        $path = str_replace('-', '', ucwords($this->resource, '-_/'));
        $path = str_replace('/', '\\', $path);

        return $this->options['endpoint_prefix'] . '\\' . $path . $this->options['endpoint_suffix'];
    }
}

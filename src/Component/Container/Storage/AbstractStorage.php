<?php

namespace Perfumer\Component\Container\Storage;

abstract class AbstractStorage
{
    // Resources array
    protected $resources = [];

    /**
     * @param string $resource
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public abstract function getParam(string $resource, string $name, $default = null);

    /**
     * @param string $name
     * @return array
     */
    public abstract function getResource(string $name): array;

    /**
     * @param string $resource
     * @param string $name
     * @param mixed $value
     */
    public abstract function saveParam(string $resource, string $name, $value): void;
}

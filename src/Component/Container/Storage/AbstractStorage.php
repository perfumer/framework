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
     * @access public
     * @abstract
     */
    public abstract function getParam($resource, $name, $default = null);

    /**
     * @param string $name
     * @return array
     * @access public
     * @abstract
     */
    public abstract function getResource($name);
}

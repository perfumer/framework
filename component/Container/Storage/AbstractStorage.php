<?php

namespace Perfumer\Component\Container\Storage;

abstract class AbstractStorage
{
    // Parameters array, divided to groups
    protected $params = [];

    // Resources array
    protected $resources = [];

    /**
     * @param string $group
     * @param string $name
     * @param mixed $default
     * @return mixed
     * @access public
     * @abstract
     */
    public abstract function getParam($group, $name, $default = null);

    /**
     * @param string $name
     * @return array
     * @access public
     * @abstract
     */
    public abstract function getResource($name);
}

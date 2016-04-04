<?php

namespace Perfumer\Component\Container\Storage;

abstract class AbstractStorage
{
    // Parameters array, divided to groups
    protected $params = [];

    /**
     * @param string $group
     * @param string $name
     * @param mixed $default
     * @return mixed
     * @access public
     * @abstract
     */
    public abstract function getParam($group, $name, $default = null);
}

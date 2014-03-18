<?php

namespace Perfumer\Cache;

abstract class AbstractCache
{
    protected $lifetime;

    public function __construct($lifetime)
    {
        $this->lifetime = $lifetime;
    }

    abstract public function get($name, $default = null);

    abstract public function set($name, $value, $lifetime = null);

    abstract public function has($name);

    abstract public function delete($name);

    protected function sanitize($value)
    {
        return str_replace(array('/', '\\', ' '), '_', $value);
    }
}
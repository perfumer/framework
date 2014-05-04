<?php

namespace Perfumer\Cache;

class DummyCache extends AbstractCache
{
    public function __construct()
    {
    }

    public function get($name, $default = null)
    {
        return $default;
    }

    public function set($name, $value, $lifetime = null)
    {
        return true;
    }

    public function has($name)
    {
        return false;
    }

    public function delete($name)
    {
        return true;
    }

    public function deleteAll()
    {
        return true;
    }
}
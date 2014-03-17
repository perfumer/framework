<?php

namespace Perfumer\Cache;

class PhpCache extends AbstractCache
{
    protected $data;

    public function __construct()
    {
    }

    public function get($name, $default = null)
    {
        if ($this->has($name))
            return $this->data[$name];

        return $default;
    }

    public function set($name, $value, $lifetime = null)
    {
        $this->data[$name] = $value;

        return true;
    }

    public function has($name)
    {
        return isset($this->data[$name]);
    }

    public function delete($name)
    {
        if ($this->has($name))
            unset($this->data[$name]);

        return true;
    }
}
<?php

namespace Perfumer;

class Stock
{
    protected $data;

    public function get($name)
    {
        if ($this->has($name))
            return $this->data[$name];

        return null;
    }

    public function set($name, $value)
    {
        $this->data[$name] = $value;

        return $this;
    }

    public function has($name)
    {
        return isset($this->data[$name]);
    }

    public function delete($name)
    {
        if ($this->has($name))
            unset($this->data[$name]);

        return $this;
    }
}
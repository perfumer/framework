<?php

namespace Perfumer\Framework\Proxy;

class Event
{
    protected $vars;

    public function __construct(array $vars = [])
    {
        $this->vars = $vars;
    }

    public function getVars()
    {
        return $this->vars;
    }

    public function getVar($name, $default = null)
    {
        return isset($this->vars[$name]) ? $this->vars[$name] : $default;
    }
}
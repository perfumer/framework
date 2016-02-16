<?php

namespace Perfumer\Framework\Proxy;

class Event
{
    /**
     * @var array
     */
    protected $vars;

    /**
     * Event constructor.
     * @param array $vars
     */
    public function __construct(array $vars = [])
    {
        $this->vars = $vars;
    }

    /**
     * @return array
     */
    public function getVars()
    {
        return $this->vars;
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getVar($name, $default = null)
    {
        return isset($this->vars[$name]) ? $this->vars[$name] : $default;
    }
}

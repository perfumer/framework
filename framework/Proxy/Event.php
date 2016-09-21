<?php

namespace Perfumer\Framework\Proxy;

class Event
{
    /**
     * @var array
     * @deprecated
     */
    protected $vars;

    /**
     * Event constructor.
     * @param array $vars
     * @deprecated
     */
    public function __construct(array $vars = [])
    {
        $this->vars = $vars;
    }

    /**
     * @return array
     * @deprecated
     */
    public function getVars()
    {
        return $this->vars;
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     * @deprecated
     */
    public function getVar($name, $default = null)
    {
        return isset($this->vars[$name]) ? $this->vars[$name] : $default;
    }
}

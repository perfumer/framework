<?php

namespace Perfumer\Framework\Proxy;

class Event
{
    /**
     * @var array
     */
    protected $vars;

    /**
     * @var bool
     */
    protected $is_cancelled = false;

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

    public function cancel()
    {
        $this->is_cancelled = true;
    }

    /**
     * @return bool
     */
    public function isCancelled()
    {
        return $this->is_cancelled;
    }
}

<?php

namespace Perfumer\Framework\Proxy;

class Deferred
{
    /**
     * @var string
     */
    protected $bundle;

    /**
     * @var string
     */
    protected $resource;

    /**
     * @var string
     */
    protected $action;

    /**
     * @var array
     */
    protected $args;

    /**
     * @var Event
     */
    protected $event;

    /**
     * @param string $bundle
     * @param string $resource
     * @param string $action
     * @param array $args
     * @param Event $event
     */
    public function __construct($bundle, $resource, $action, $args = [], Event $event = null)
    {
        $this->bundle = (string) $bundle;
        $this->resource = (string) $resource;
        $this->action = (string) $action;
        $this->args = (array) $args;
        $this->event = $event;
    }

    /**
     * @return string
     */
    public function getBundle()
    {
        return $this->bundle;
    }

    /**
     * @return string
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return array
     */
    public function getArgs()
    {
        return $this->event instanceof Event ? [$this->event] : $this->args;
    }

    /**
     * @return Event|null
     */
    public function getEvent()
    {
        return $this->event;
    }
}

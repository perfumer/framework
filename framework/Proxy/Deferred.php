<?php

namespace Perfumer\Framework\Proxy;

class Deferred extends Attributes
{
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
        parent::__construct($bundle, $resource, $action, $args);

        $this->event = $event;
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

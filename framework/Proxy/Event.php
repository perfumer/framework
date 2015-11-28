<?php

namespace Perfumer\Framework\Proxy;

class Event
{
    protected $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }
}
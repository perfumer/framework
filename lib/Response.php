<?php

namespace Perfumer;

class Response
{
    protected $headers = [];
    protected $body;

    public function sendHeaders()
    {
        foreach ($this->headers as $name => $value)
            header($name . ': ' . $value);
    }

    public function addHeader($name, $value)
    {
        $this->headers[$name] = $value;

        return $this;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }
}
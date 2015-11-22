<?php

namespace Perfumer\MVC\Proxy;

class Response
{
    protected $content;

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }
}
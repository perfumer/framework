<?php

namespace Perfumer\Framework\Proxy;

class Response
{
    protected $status = true;

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

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = (bool) $status;

        return $this;
    }
}
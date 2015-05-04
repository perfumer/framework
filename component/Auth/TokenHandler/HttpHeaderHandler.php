<?php

namespace Perfumer\Component\Auth\TokenHandler;

class HttpHeaderHandler extends AbstractHandler
{
    protected $header;

    public function __construct($header)
    {
        $this->header = $header;
    }

    public function getToken()
    {
        return isset($_SERVER[$this->header]) ? $_SERVER[$this->header] : null;
    }

    public function setToken($token)
    {
    }

    public function deleteToken()
    {
    }

    public function getTokenLifetime()
    {
        return 0;
    }
}
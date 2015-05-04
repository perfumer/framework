<?php

namespace Perfumer\Component\Auth\TokenHandler;

class HttpHeaderHandler extends AbstractHandler
{
    protected $options = [];

    public function __construct($options = [])
    {
        $default_options = [
            'header' => 'HTTP_TOKEN',
            'lifetime' => 3600
        ];

        $this->options = array_merge($default_options, $options);
    }

    public function getToken()
    {
        return isset($_SERVER[$this->options['header']]) ? $_SERVER[$this->options['header']] : null;
    }

    public function setToken($token)
    {
    }

    public function deleteToken()
    {
    }

    public function getTokenLifetime()
    {
        return $this->options['lifetime'];
    }
}
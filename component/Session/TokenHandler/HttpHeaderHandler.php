<?php

namespace Perfumer\Component\Session\TokenHandler;

class HttpHeaderHandler extends AbstractHandler
{
    /**
     * @var array
     */
    protected $options = [];

    /**
     * HttpHeaderHandler constructor.
     * @param array $options
     */
    public function __construct($options = [])
    {
        $default_options = [
            'header' => 'HTTP_TOKEN',
            'lifetime' => 3600
        ];

        $this->options = array_merge($default_options, $options);
    }

    /**
     * @return string|null
     */
    public function getToken()
    {
        return isset($_SERVER[$this->options['header']]) ? $_SERVER[$this->options['header']] : null;
    }

    /**
     * @param string $token
     */
    public function setToken($token)
    {
    }

    public function deleteToken()
    {
    }

    /**
     * @return int
     */
    public function getTokenLifetime()
    {
        return $this->options['lifetime'];
    }
}

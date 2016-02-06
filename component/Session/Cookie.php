<?php

namespace Perfumer\Component\Session;

class Cookie
{
    protected $options = [];

    public function __construct(array $options = [])
    {
        $default_options = [
            'expiration' => 3600,
            'path' => '/',
            'domain' => null,
            'secure' => false,
            'http_only' => true
        ];

        $this->options = array_merge($default_options, $options);
    }

    public function getPath()
    {
        return $this->options['path'];
    }

    public function getDomain()
    {
        return $this->options['domain'];
    }

    public function getSecure()
    {
        return $this->options['secure'];
    }

    public function getHttpOnly()
    {
        return $this->options['http_only'];
    }

    public function get($key, $default = null)
    {
        return isset($_COOKIE[$key]) ? $_COOKIE[$key] : $default;
    }

    public function set($name, $value, $expiration = null)
    {
        if ($expiration === null)
            $expiration = $this->options['expiration'];

        if ($expiration > 0)
            $expiration += time();

        return setcookie($name, $value, $expiration, $this->options['path'], $this->options['domain'], $this->options['secure'], $this->options['http_only']);
    }

    public function delete($name)
    {
        unset($_COOKIE[$name]);

        return setcookie($name, null, -86400, $this->options['path'], $this->options['domain'], $this->options['secure'], $this->options['http_only']);
    }
}

<?php

namespace Perfumer\Component\Auth;

class Cookie
{
    /**
     * @var array
     */
    protected $options = [];

    /**
     * Cookie constructor.
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $default_options = [
            'lifetime' => 3600,
            'path' => '/',
            'domain' => null,
            'secure' => false,
            'http_only' => true
        ];

        $this->options = array_merge($default_options, $options);
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->options['path'];
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->options['domain'];
    }

    /**
     * @return bool
     */
    public function getSecure()
    {
        return $this->options['secure'];
    }

    /**
     * @return bool
     */
    public function getHttpOnly()
    {
        return $this->options['http_only'];
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return isset($_COOKIE[$key]) ? $_COOKIE[$key] : $default;
    }

    /**
     * @param string $name
     * @param string $value
     * @param int|null $lifetime
     * @return bool
     */
    public function set($name, $value, $lifetime = null)
    {
        if ($lifetime === null) {
            $lifetime = $this->options['lifetime'];
        }

        if ($lifetime > 0) {
            $lifetime += time();
        }

        return setcookie($name, $value, $lifetime, $this->options['path'], $this->options['domain'], $this->options['secure'], $this->options['http_only']);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function delete($name)
    {
        unset($_COOKIE[$name]);

        return setcookie($name, null, -86400, $this->options['path'], $this->options['domain'], $this->options['secure'], $this->options['http_only']);
    }
}

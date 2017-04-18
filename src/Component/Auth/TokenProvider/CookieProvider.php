<?php

namespace Perfumer\Component\Auth\TokenProvider;

use Perfumer\Component\Auth\Cookie;

class CookieProvider extends AbstractProvider
{
    /**
     * @var Cookie
     */
    protected $cookie;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * CookieHandler constructor.
     * @param Cookie $cookie
     * @param array $options
     */
    public function __construct(Cookie $cookie, $options = [])
    {
        $this->cookie = $cookie;

        $default_options = [
            'name' => '_session',
            'lifetime' => 3600
        ];

        $this->options = array_merge($default_options, $options);
    }

    /**
     * @return string|null
     */
    public function getToken()
    {
        return $this->cookie->get($this->options['name']);
    }

    /**
     * @param string $token
     */
    public function setToken($token)
    {
        $this->cookie->set($this->options['name'], $token, $this->options['lifetime']);
    }

    public function deleteToken()
    {
        $this->cookie->delete($this->options['name']);
    }

    /**
     * @return int
     */
    public function getTokenLifetime()
    {
        return $this->options['lifetime'];
    }
}

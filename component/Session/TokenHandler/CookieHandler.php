<?php

namespace Perfumer\Component\Session\TokenHandler;

use Perfumer\Component\Session\Cookie;

class CookieHandler extends AbstractHandler
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
            'lifetime' => 3600
        ];

        $this->options = array_merge($default_options, $options);
    }

    /**
     * @return string|null
     */
    public function getToken()
    {
        return $this->cookie->get('_session');
    }

    /**
     * @param string $token
     */
    public function setToken($token)
    {
        $this->cookie->set('_session', $token, $this->options['lifetime']);
    }

    public function deleteToken()
    {
        $this->cookie->delete('_session');
    }

    /**
     * @return int
     */
    public function getTokenLifetime()
    {
        return $this->options['lifetime'];
    }
}

<?php

namespace Perfumer\Component\Session\TokenHandler;

use Perfumer\Component\Session\Cookie;

class CookieHandler extends AbstractHandler
{
    protected $cookie;

    protected $options = [];

    public function __construct(Cookie $cookie, $options = [])
    {
        $this->cookie = $cookie;

        $default_options = [
            'lifetime' => 3600
        ];

        $this->options = array_merge($default_options, $options);
    }

    public function getToken()
    {
        return $this->cookie->get('_session');
    }

    public function setToken($token)
    {
        $this->cookie->set('_session', $token, $this->options['lifetime']);
    }

    public function deleteToken()
    {
        $this->cookie->delete('_session');
    }

    public function getTokenLifetime()
    {
        return $this->options['lifetime'];
    }
}

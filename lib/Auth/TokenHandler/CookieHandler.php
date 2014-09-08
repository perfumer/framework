<?php

namespace Perfumer\Auth\TokenHandler;

use Perfumer\Helper\Cookie;

class CookieHandler extends AbstractHandler
{
    protected $cookie;

    protected $cookie_lifetime;

    public function __construct(Cookie $cookie, $cookie_lifetime = 3600)
    {
        $this->cookie = $cookie;
        $this->cookie_lifetime = (int) $cookie_lifetime;
    }

    public function getToken()
    {
        return $this->cookie->get('_session');
    }

    public function setToken($token)
    {
        $this->cookie->set('_session', $token, $this->cookie_lifetime);
    }

    public function deleteToken()
    {
        $this->cookie->delete('_session');
    }

    public function getTokenLifetime()
    {
        return $this->cookie_lifetime;
    }
}
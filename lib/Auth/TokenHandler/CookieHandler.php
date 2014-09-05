<?php

namespace Perfumer\Auth\TokenHandler;

use Perfumer\Helper\Cookie;

class CookieHandler extends AbstractHandler
{
    protected $cookie;

    public function __construct(Cookie $cookie)
    {
        $this->cookie = $cookie;
    }

    public function getToken()
    {
        return $this->cookie->get('_session');
    }

    public function setToken($token)
    {
        $this->cookie->set('_session', $token);
    }

    public function deleteToken()
    {
        $this->cookie->delete('_session');
    }
}
<?php

namespace Perfumer\Session\Token\Provider;

use Perfumer\Helper\Cookie;

class CookieProvider extends AbstractProvider
{
    protected $cookie;
    protected $session_name;

    public function __construct(Cookie $cookie, $session_name)
    {
        $this->cookie = $cookie;
        $this->session_name = $session_name;
    }

    public function getToken()
    {
        return $this->$cookie->get($this->session_name);
    }
}
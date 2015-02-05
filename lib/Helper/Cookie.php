<?php

namespace Perfumer\Helper;

/**
 * Fork of Kohana_Cookie class, written in non-static way.
 *
 * @package    Kohana
 * @category   Helpers
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Cookie
{
    protected $expiration = 3600;
    protected $path = '/';
    protected $domain = null;
    protected $secure = false;
    protected $httponly = true;

    public function __construct(array $params = [])
    {
        if (isset($params['expiration']))
            $this->expiration = (int) $params['expiration'];

        if (isset($params['path']))
            $this->path = (string) $params['path'];

        if (isset($params['domain']) && $params['domain'] !== null)
            $this->domain = (string) $params['domain'];

        if (isset($params['secure']))
            $this->secure = (bool) $params['secure'];

        if (isset($params['httponly']))
            $this->httponly = (bool) $params['httponly'];
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getDomain()
    {
        return $this->domain;
    }

    public function getSecure()
    {
        return $this->secure;
    }

    public function getHttponly()
    {
        return $this->httponly;
    }

    public function get($key, $default = null)
    {
        return isset($_COOKIE[$key]) ? $_COOKIE[$key] : $default;
    }

    public function set($name, $value, $expiration = null)
    {
        if ($expiration === null)
            $expiration = $this->expiration;

        if ($expiration !== 0)
            $expiration += time();

        return setcookie($name, $value, $expiration, $this->path, $this->domain, $this->secure, $this->httponly);
    }

    public function delete($name)
    {
        unset($_COOKIE[$name]);

        return setcookie($name, null, -86400, $this->path, $this->domain, $this->secure, $this->httponly);
    }
}
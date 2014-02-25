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
    protected $salt = null;
    protected $expiration = 3600;
    protected $path = '/';
    protected $domain = null;
    protected $secure = false;
    protected $httponly = true;

    public function __construct(array $params = [])
    {
        $options = ['salt', 'expiration', 'path', 'domain'];

        foreach ($options as $option)
        {
            if (isset($params[$option]))
                $this->{$option} = $params[$option];
        }

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
        if (!isset($_COOKIE[$key]))
            return $default;

        $cookie = $_COOKIE[$key];

        $split = strlen($this->makeSalt($key, null));

        if (isset($cookie[$split]) && $cookie[$split] === '~')
        {
            list($hash, $value) = explode('~', $cookie, 2);

            if ($this->makeSalt($key, $value) === $hash)
                return $value;

            $this->delete($key);
        }

        return $default;
    }

    public function set($name, $value, $expiration = null)
    {
        if ($expiration === null)
            $expiration = $this->expiration;

        if ($expiration !== 0)
            $expiration += time();

        $value = $this->makeSalt($name, $value) . '~' . $value;

        return setcookie($name, $value, $expiration, $this->path, $this->domain, $this->secure, $this->httponly);
    }

    public function delete($name)
    {
        unset($_COOKIE[$name]);

        return setcookie($name, null, -86400, $this->path, $this->domain, $this->secure, $this->httponly);
    }

    protected function makeSalt($name, $value)
    {
        $agent = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : 'unknown';

        return sha1($agent . $name . $value . $this->salt);
    }
}
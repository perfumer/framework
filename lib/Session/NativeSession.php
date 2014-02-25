<?php

namespace Perfumer\Session;

use Perfumer\Helper\Cookie;

/**
 * Fork of Kohana_Session_Native class
 *
 * @package    Kohana
 * @category   Session
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class NativeSession extends AbstractSession
{
    protected $cookie;

    public function __construct(Cookie $cookie, $name, $lifetime)
    {
        $this->cookie = $cookie;

        parent::__construct($name, $lifetime);

        session_set_cookie_params($this->lifetime, $this->cookie->getPath(), $this->cookie->getDomain(), $this->cookie->getSecure(), $this->cookie->getHttponly());
        session_cache_limiter(false);
        session_name($this->name);
    }

    public function getId()
    {
        return session_id();
    }

    protected function _start($id = null)
    {
        if ($id)
            session_id($id);

        session_start();

        $this->data = &$_SESSION;
    }

    protected function _regenerate()
    {
        session_regenerate_id();

        return session_id();
    }

    protected function _write()
    {
        session_write_close();

        return true;
    }

    protected function _restart()
    {
        $status = session_start();

        $this->data = &$_SESSION;

        return $status;
    }

    protected function _destroy()
    {
        session_destroy();

        $status = !session_id();

        if ($status)
        {
            $this->cookie->delete($this->name);
        }

        return $status;
    }
}
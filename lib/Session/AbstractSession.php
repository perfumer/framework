<?php

namespace Perfumer\Session;

/**
 * Fork of Kohana_Session class
 *
 * @package    Kohana
 * @category   Session
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
abstract class AbstractSession
{
    protected $cookie_name;
    protected $cookie_lifetime;
    protected $data = [];
    protected $is_started = false;
    protected $is_destroyed = false;

    public function __construct($cookie_name, $cookie_lifetime)
    {
        $this->cookie_name = (string) $cookie_name;
        $this->cookie_lifetime = (int) $cookie_lifetime;
    }

    public function isStarted()
    {
        return $this->is_started;
    }

    public function &asArray()
    {
        return $this->data;
    }

    public function getId()
    {
        return null;
    }

    public function getName()
    {
        return $this->cookie_name;
    }

    public function get($key, $default = null)
    {
        return array_key_exists($key, $this->data) ? $this->data[$key] : $default;
    }

    public function getOnce($key, $default = null)
    {
        $value = $this->get($key, $default);

        unset($this->data[$key]);

        return $value;
    }

    public function set($key, $value)
    {
        $this->data[$key] = $value;

        return $this;
    }

    public function bind($key, &$value)
    {
        $this->data[$key] = &$value;

        return $this;
    }

    public function delete($key)
    {
        $args = func_get_args();

        foreach ($args as $key)
        {
            unset($this->data[$key]);
        }

        return $this;
    }

    public function start($id = null)
    {
        try
        {
            $this->_start($id);

            $this->is_started = true;

            register_shutdown_function([$this, 'write']);
        }
        catch (\Exception $e)
        {
            throw new SessionException('Error reading session data. Session data is likely corrupted.');
        }
    }

    public function regenerate()
    {
        return $this->_regenerate();
    }

    public function write()
    {
        if (headers_sent() || $this->is_destroyed)
        {
            return false;
        }

        $this->data['last_active'] = time();

        try
        {
            return $this->_write();
        }
        catch (\Exception $e)
        {
            // Log & ignore all errors when a write fails
            //Kohana::$log->add(Log::ERROR, Kohana_Exception::text($e))->write();

            return false;
        }
    }

    public function destroy()
    {
        if ($this->is_destroyed === false)
        {
            if ($this->is_destroyed = $this->_destroy())
            {
                $this->data = [];
            }
        }

        return $this->is_destroyed;
    }

    public function restart()
    {
        if ($this->is_destroyed === false)
        {
            $this->destroy();
        }

        $this->is_destroyed = false;

        return $this->_restart();
    }

    abstract protected function _start($id = null);

    abstract protected function _regenerate();

    abstract protected function _write();

    abstract protected function _destroy();

    abstract protected function _restart();
}
<?php

namespace Perfumer\Component\Session;

class Session
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var mixed
     */
    protected $shared_id;

    /**
     * @var bool
     */
    protected $is_retrieved = false;

    /**
     * @var Pool
     */
    protected $pool;

    /**
     * Session constructor.
     * @param string $id
     * @param Pool $pool
     */
    public function __construct($id, Pool $pool)
    {
        $this->id = $id;
        $this->pool = $pool;
    }

    public function refresh()
    {
        if ($this->is_retrieved === false) {
            $this->retrieveSharedId();
        }

        if ($this->shared_id) {
            $this->pool->getCache()->getItem('_session/' . $this->id)->set($this->shared_id, $this->getLifetime());
        }
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getSharedId()
    {
        if ($this->is_retrieved === false) {
            $this->retrieveSharedId();
        }

        return $this->shared_id;
    }

    /**
     * @param string $shared_id
     */
    public function setSharedId($shared_id)
    {
        $this->shared_id = $shared_id;

        $this->pool->getCache()->getItem('_session/' . $this->id)->set($shared_id, $this->getLifetime());
    }

    /**
     * @param string $key
     * @param mixed $default
     * @param bool $clear
     * @return mixed
     */
    public function get($key, $default = null, $clear = false)
    {
        if ($this->is_retrieved === false) {
            $this->retrieveSharedId();
        }

        $value = $default;

        if ($this->shared_id) {
            $cache = $this->getCache($key);

            if (!$cache->isMiss()) {
                $value = $cache->get();
            }

            if ($clear === true) {
                $cache->clear();
            }
        }

        return $value;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param int $lifetime
     */
    public function set($key, $value, $lifetime = null)
    {
        if ($this->is_retrieved === false) {
            $this->retrieveSharedId();
        }

        if ($this->shared_id) {
            $lifetime = $lifetime ?: $this->getLifetime();

            $this->getCache($key)->set($value, $lifetime);
        }
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        if ($this->is_retrieved === false) {
            $this->retrieveSharedId();
        }

        if ($this->shared_id) {
            return !$this->getCache($key)->isMiss();
        } else {
            return false;
        }
    }

    /**
     * @param string $key
     * @return $this
     */
    public function delete($key)
    {
        if ($this->is_retrieved === false) {
            $this->retrieveSharedId();
        }

        if ($this->shared_id) {
            $this->getCache($key)->clear();
        }
    }

    public function destroy()
    {
        $this->pool->getCache()->getItem('_session/' . $this->id)->clear();
    }

    /**
     * @return int
     */
    public function getLifetime()
    {
        return $this->pool->getLifetime();
    }

    /**
     * @param string $field
     * @return \Stash\Pool
     */
    protected function getCache($field)
    {
        return $this->pool->getCache()->getItem('_session_shared/' . (string) $this->shared_id . '/' . $field);
    }

    protected function retrieveSharedId()
    {
        $this->shared_id = $this->pool->getCache()->getItem('_session/' . $this->id)->get();

        $this->is_retrieved = true;
    }
}

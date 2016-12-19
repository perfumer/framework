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
     * @var Pool
     */
    protected $pool;

    /**
     * Session constructor.
     * @param string $id
     * @param mixed $shared_id
     * @param Pool $pool
     */
    public function __construct($id, $shared_id, Pool $pool)
    {
        $this->id = $id;
        $this->shared_id = $shared_id;
        $this->pool = $pool;

        $this->pool->getCache()->getItem('_session/' . $this->id)->set($this->shared_id, $this->getLifetime());
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
        return $this->shared_id;
    }

    /**
     * @param string $key
     * @param mixed $default
     * @param bool $clear
     * @return mixed
     */
    public function get($key, $default = null, $clear = false)
    {
        $cache = $this->getCache($key);

        $value = $cache->isMiss() ? $default : $cache->get();

        if ($clear === true) {
            $cache->clear();
        }

        return $value;
    }

    /**
     * @deprecated
     */
    public function getOnce($key, $default = null)
    {
        return $this->get($key, $default, true);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param int $lifetime
     */
    public function set($key, $value, $lifetime = null)
    {
        $lifetime = $lifetime ?: $this->getLifetime();

        $this->getCache($key)->set($value, $lifetime);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return !$this->getCache($key)->isMiss();
    }

    /**
     * @param string $key
     * @return $this
     */
    public function delete($key)
    {
        $this->getCache($key)->clear();
    }

    public function destroy()
    {
        $this->pool->getCache()->getItem('_session/' . $this->id)->clear();
    }

    /**
     * @param string $field
     * @return \Stash\Pool
     */
    public function getCache($field)
    {
        return $this->pool->getCache()->getItem('_session_shared/' . (string) $this->shared_id . '/' . $field);
    }

    /**
     * @return int
     */
    public function getLifetime()
    {
        return $this->pool->getLifetime();
    }
}

<?php

namespace Perfumer\Component\Session;

class Session
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var Pool
     */
    protected $pool;

    public function __construct($id, Pool $pool)
    {
        $this->id = $id;
        $this->pool = $pool;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $key
     * @param $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $item = $this->getCache()->getItem('_session/' . $this->id . '/' . $key);

        return $item->isMiss() ? $default : $item->get();
    }

    /**
     * @param $key
     * @param $default
     * @return mixed
     */
    public function getOnce($key, $default = null)
    {
        $value = $this->get($key, $default);

        $this->delete($key);

        return $value;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function set($key, $value)
    {
        $this->getCache()->getItem('_session/' . $this->id)->set(time() + $this->getLifetime(), $this->getLifetime());
        $this->getCache()->getItem('_session/' . $this->id . '/' . $key)->set($value, $this->getLifetime());

        return $this;
    }

    /**
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        return !$this->getCache()->getItem('_session/' . $this->id . '/' . $key)->isMiss();
    }

    /**
     * @param $key
     */
    public function delete($key)
    {
        $this->getCache()->getItem('_session/' . $this->id . '/' . $key)->clear();
    }

    public function destroy()
    {
        $this->getCache()->getItem('_session/' . $this->id)->clear();
    }

    public function getCache()
    {
        return $this->pool->getCache();
    }

    public function getLifetime()
    {
        return $this->pool->getLifetime();
    }
}

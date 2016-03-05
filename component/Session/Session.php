<?php

namespace Perfumer\Component\Session;

class Session
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var array
     */
    protected $data;

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

        $this->refresh();
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return isset($this->data[$key]) ? $this->data[$key] : $default;
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getOnce($key, $default = null)
    {
        $value = $this->get($key, $default);

        $this->delete($key);

        return $value;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function set($key, $value)
    {
        $this->data[$key] = $value;

        $this->persist();

        return $this;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * @param string $key
     * @return $this
     */
    public function delete($key)
    {
        if (isset($this->data[$key])) {
            unset($this->data[$key]);

            $this->persist();
        }

        return $this;
    }

    public function destroy()
    {
        $this->data = [];

        $this->getCache()->clear();
    }

    public function persist()
    {
        $this->getCache()->set($this->data, $this->getLifetime());
    }

    public function refresh()
    {
        $cache = $this->getCache();

        $this->data = $cache->isMiss() ? [] : $cache->get();
    }

    /**
     * @return \Stash\Pool
     */
    public function getCache()
    {
        return $this->pool->getCache()->getItem('_session/' . $this->id);
    }

    /**
     * @return int
     */
    public function getLifetime()
    {
        return $this->pool->getLifetime();
    }
}

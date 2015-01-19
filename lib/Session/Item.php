<?php

namespace Perfumer\Session;

use Perfumer\Session\Core as Session;
use Stash\Pool as Cache;

class Item
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var Cache
     */
    protected $cache;

    protected $id;

    protected $lifetime;

    protected $is_destroyed = false;

    public function __construct(Session $session, Cache $cache, array $options)
    {
        $this->session = $session;
        $this->cache = $cache;

        $this->id = $options['id'];
        $this->lifetime = (int) $options['lifetime'];
    }

    public function getId()
    {
        return $this->id;
    }

    public function get($key, $default = null)
    {
        if ($this->is_destroyed)
            return $default;

        $item = $this->cache->getItem('_session/' . $this->id . '/' . $key);

        return $item->isMiss() ? $default : $item->get();
    }

    public function getOnce($key, $default = null)
    {
        $value = $this->get($key, $default);

        $this->delete($key);

        return $value;
    }

    public function set($key, $value)
    {
        if (!$this->is_destroyed)
            $this->cache->getItem('_session/' . $this->id . '/' . $key)->set($value, $this->lifetime);

        return $this;
    }

    public function has($key)
    {
        if ($this->is_destroyed)
            return false;

        return !$this->cache->getItem('_session/' . $this->id . '/' . $key)->isMiss();
    }

    public function delete($key)
    {
        if (!$this->is_destroyed)
            $this->cache->getItem('_session/' . $this->id . '/' . $key)->clear();

        return $this;
    }

    public function destroy()
    {
        if (!$this->is_destroyed)
            $this->cache->getItem('_session/' . $this->id)->clear();

        $this->is_destroyed = true;
    }
}
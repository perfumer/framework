<?php

namespace Perfumer\Component\Session;

class Item
{
    /**
     * @var Session
     */
    protected $session;

    protected $id;

    protected $is_destroyed = false;

    public function __construct(Session $session, array $options)
    {
        $this->session = $session;

        $this->id = $options['id'];
    }

    public function getId()
    {
        return $this->id;
    }

    public function get($key, $default = null)
    {
        if ($this->is_destroyed)
            return $default;

        $item = $this->getCache()->getItem('_session/' . $this->id . '/' . $key);

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
        {
            $this->getCache()->getItem('_session/' . $this->id . '/' . $key)->set($value, $this->getLifetime());
            $this->getCache()->getItem('_session/' . $this->id)->set(time() + $this->$this->getLifetime(), $this->$this->getLifetime());
        }

        return $this;
    }

    public function has($key)
    {
        if ($this->is_destroyed)
            return false;

        return !$this->getCache()->getItem('_session/' . $this->id . '/' . $key)->isMiss();
    }

    public function delete($key)
    {
        if (!$this->is_destroyed)
            $this->getCache()->getItem('_session/' . $this->id . '/' . $key)->clear();

        return $this;
    }

    public function destroy()
    {
        if (!$this->is_destroyed)
            $this->getCache()->getItem('_session/' . $this->id)->clear();

        $this->is_destroyed = true;
    }

    public function isDestroyed()
    {
        return $this->is_destroyed;
    }

    public function getCache()
    {
        return $this->session->getCache();
    }

    public function getLifetime()
    {
        return $this->session->getLifetime();
    }
}

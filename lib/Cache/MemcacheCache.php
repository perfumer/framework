<?php

namespace Perfumer\Cache;

use Perfumer\Cache\Exception\CacheException;

class MemcacheCache extends AbstractCache
{
    const MAX_LIFETIME = 2592000;

    protected $memcache;

    public function __construct(array $options)
    {
        if (!extension_loaded('memcache'))
            throw new CacheException('Memcache PHP extention not loaded.');

        parent::__construct($options['lifetime']);

        if (!isset($options['servers']))
            throw new CacheException('No Memcache servers defined in configuration.');

        $this->memcache = new \Memcache();

        foreach ($options['servers'] as $server)
        {
            if ( ! $this->memcache->addServer($server['host'], $server['port'], $server['persistent'], $server['weight'], $server['timeout'], $server['retry_interval'], $server['status']))
                throw new CacheException('Memcache could not connect to host "' . $server['host'] . '" using port "' . $server['port'] . '".');
        }
    }

    public function get($name, $default = null)
    {
        $value = $this->memcache->get($this->sanitize($name));

        if ($value === false)
            $value = $default;

        return $value;
    }

    public function set($name, $value, $lifetime = null)
    {
        if ($lifetime === null)
            $lifetime = $this->lifetime;

        if ($lifetime > self::MAX_LIFETIME)
        {
            $lifetime = self::MAX_LIFETIME + time();
        }
        elseif ($lifetime > 0)
        {
            $lifetime += time();
        }
        else
        {
            $lifetime = 0;
        }

        return $this->memcache->set($this->sanitize($name), $value, false, $lifetime);
    }

    public function delete($name)
    {
        return $this->memcache->delete($this->sanitize($name));
    }

    public function deleteAll()
    {
        $this->memcache->flush();

        sleep(1);
    }

    public function has($name)
    {
        return $this->get($name) !== null;
    }

    public function increment($name, $step = 1)
    {
        $this->memcache->increment($name, $step);
    }

    public function decrement($name, $step = 1)
    {
        $this->memcache->decrement($name, $step);
    }
}
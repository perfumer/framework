<?php

namespace Perfumer\Component\Auth;

use Perfumer\Helper\Text;
use Stash\Pool as Cache;

class Session
{
    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @var int
     */
    protected $lifetime = 3600;

    /**
     * @var string
     */
    protected $cache_prefix = '_session';

    /**
     * @param Cache $cache
     * @param array $options
     */
    public function __construct(Cache $cache, array $options = [])
    {
        $this->cache = $cache;

        if (isset($options['lifetime'])) {
            $this->lifetime = (int) $options['lifetime'];
        }

        if (isset($options['cache_prefix'])) {
            $this->cache_prefix = (string) $options['cache_prefix'];
        }
    }

    /**
     * @param string $id
     * @return mixed
     */
    public function get($id)
    {
        $item = $this->cache->getItem($this->cache_prefix . '/' . $id);

        return $item->isHit() ? $item->get() : null;
    }

    /**
     * @param string $id
     * @param string $shared_id
     */
    public function set($id, $shared_id)
    {
        $item = $this->cache->getItem($this->cache_prefix . '/' . $id);
        $item->set($shared_id);
        $item->expiresAfter($this->lifetime);
        $item->save();
    }

    /**
     * @param string $id
     * @return bool
     */
    public function has($id)
    {
        return $this->cache->getItem($this->cache_prefix . '/' . $id)->isHit();
    }

    /**
     * @param string $id
     */
    public function destroy($id)
    {
        $this->cache->getItem($this->cache_prefix . '/' . $id)->clear();
    }

    /**
     * @return string
     */
    public function generateId()
    {
        do {
            $id = Text::generateString(100);

            $item = $this->cache->getItem($this->cache_prefix . '/' . $id);
        } while ($item->isHit());

        return $id;
    }
}

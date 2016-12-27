<?php

namespace Perfumer\Component\Session;

use Perfumer\Helper\Text;
use Stash\Pool as Cache;

class Pool
{
    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @var array
     */
    protected $sessions = [];

    /**
     * @var int
     */
    protected $lifetime = 3600;

    /**
     * @var string
     */
    protected $session_cache_prefix = '_session';

    /**
     * @var string
     */
    protected $shared_cache_prefix = '_session_shared';

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

        if (isset($options['session_cache_prefix'])) {
            $this->session_cache_prefix = (string) $options['session_cache_prefix'];
        }

        if (isset($options['shared_cache_prefix'])) {
            $this->shared_cache_prefix = (string) $options['shared_cache_prefix'];
        }
    }

    /**
     * @param string $id
     * @return Session
     */
    public function get($id = null)
    {
        if ($id === null) {
            $id = $this->generateId();
        }

        if (isset($this->sessions[$id])) {
            return $this->sessions[$id];
        }

        $this->sessions[$id] = new Session($id, $this);

        return $this->sessions[$id];
    }

    /**
     * @param string $id
     * @return bool
     */
    public function has($id)
    {
        return !$this->cache->getItem($this->session_cache_prefix . '/' . $id)->isMiss();
    }

    /**
     * @param string $id
     */
    public function destroy($id)
    {
        $this->get($id)->destroy();
    }

    /**
     * @return Cache
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @return int
     */
    public function getLifetime()
    {
        return $this->lifetime;
    }

    /**
     * @return string
     */
    public function getSessionCachePrefix()
    {
        return $this->session_cache_prefix;
    }

    /**
     * @return string
     */
    public function getSharedCachePrefix()
    {
        return $this->shared_cache_prefix;
    }

    /**
     * @return string
     */
    protected function generateId()
    {
        do {
            $id = Text::generateString(20);

            $item = $this->cache->getItem($this->session_cache_prefix . '/' . $id);
        } while (!$item->isMiss());

        return $id;
    }
}

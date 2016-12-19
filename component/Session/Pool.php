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
     * @param Cache $cache
     * @param array $options
     */
    public function __construct(Cache $cache, array $options = [])
    {
        $this->cache = $cache;

        if (isset($options['lifetime'])) {
            $this->lifetime = (int) $options['lifetime'];
        }
    }

    /**
     * @param string $id
     * @param mixed $shared_id
     * @return Session
     */
    public function get($id = null, $shared_id = null)
    {
        if ($id === null) {
            $id = $this->generateId();
        }

        if (isset($this->sessions[$id])) {
            return $this->sessions[$id];
        }

        if ($shared_id === null) {
            $shared_id = $this->generateSharedId();
        }

        $this->sessions[$id] = new Session($id, $shared_id, $this);

        return $this->sessions[$id];
    }

    /**
     * @param string $id
     * @return bool
     */
    public function has($id)
    {
        return !$this->cache->getItem('_session/' . $id)->isMiss();
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
    protected function generateId()
    {
        do {
            $id = Text::generateString(20);

            $item = $this->cache->getItem('_session/' . $id);
        } while (!$item->isMiss());

        return $id;
    }

    /**
     * @return string
     */
    protected function generateSharedId()
    {
        do {
            $shared_id = Text::generateString(20);

            $item = $this->cache->getItem('_session_shared/' . $shared_id);
        } while (!$item->isMiss());

        return $shared_id;
    }
}

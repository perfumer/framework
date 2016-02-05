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

    public function __construct(Cache $cache, array $options = [])
    {
        $this->cache = $cache;

        if (isset($options['lifetime'])) {
            $this->lifetime = (int) $options['lifetime'];
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
        } while (!$item->isMiss() || isset($this->sessions[$id]));

        return $id;
    }
}

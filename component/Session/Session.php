<?php

namespace Perfumer\Component\Session;

use Perfumer\Helper\Text;
use Stash\Pool as Cache;

class Session
{
    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @var array
     */
    protected $items = [];

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
     * @return Item
     */
    public function get($id = null)
    {
        if ($id === null) {
            $id = $this->generateId();
        }

        if (isset($this->items[$id])) {
            return $this->items[$id];
        }

        $this->items[$id] = new Item($id, $this);

        return $this->items[$id];
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
        } while (!$item->isMiss() || isset($this->items[$id]));

        return $id;
    }
}

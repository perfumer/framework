<?php

namespace Perfumer\Component\Session;

use Perfumer\Helper\Text;
use Stash\Pool as Cache;

class Core
{
    /**
     * @var \Stash\Pool
     */
    protected $cache;

    protected $items = [];

    protected $lifetime = 3600;

    public function __construct(Cache $cache, array $options = [])
    {
        $this->cache = $cache;

        if (isset($options['lifetime']))
            $this->lifetime = (int) $options['lifetime'];
    }

    public function get($id = null)
    {
        if ($id === null)
            $id = $this->generateId();

        if (isset($this->items[$id]))
            return $this->items[$id];

        $this->items[$id] = new Item($this, $this->cache, [
            'id' => $id,
            'lifetime' => $this->lifetime
        ]);

        return $this->items[$id];
    }

    public function has($id)
    {
        return !$this->cache->getItem('_session/' . $id)->isMiss();
    }

    public function destroy($id)
    {
        $this->get($id)->destroy();
    }

    protected function generateId()
    {
        do
        {
            $id = Text::generateString(20);

            $item = $this->cache->getItem('_session/' . $id);
        }
        while (!$item->isMiss() || isset($this->items[$id]));

        return $id;
    }
}
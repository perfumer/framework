<?php

namespace Perfumer\Session;

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

        $item = new Item($this, $this->cache, [
            'id' => $id,
            'lifetime' => $this->lifetime
        ]);

        $this->items[$id] = $item;

        return $item;
    }

    public function destroy($id)
    {
        if (!isset($this->items[$id]))
            $this->get($id);

        $this->items[$id]->destroy();
    }

    protected function generateId()
    {
        $id = uniqid('', true);

        do
        {
            $id = md5($id);

            $item = $this->cache->getItem('_session/' . $id);
        }
        while (!$item->isMiss());

        return $id;
    }
}
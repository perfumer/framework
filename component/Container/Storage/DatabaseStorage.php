<?php

namespace Perfumer\Component\Container\Storage;

use App\Model\ResourceQuery;
use Perfumer\Component\Container\Exception\ContainerException;

class DatabaseStorage extends AbstractStorage
{
    /**
     * @param string $resource
     * @param string $name
     * @param mixed $default
     * @return mixed
     * @access public
     */
    public function getParam($resource, $name, $default = null)
    {
        $this->loadResource($resource);

        return isset($this->resources[$resource][$name]) ? $this->resources[$resource][$name] : $default;
    }

    /**
     * @param string $name
     * @return array
     * @throws ContainerException
     * @access public
     */
    public function getResource($name)
    {
        $this->loadResource($name);

        return $this->resources[$name];
    }

    protected function loadResource($name)
    {
        if (!isset($this->resources[$name])) {
            $value = ResourceQuery::create()
                ->filterByName($name)
                ->select(['value'])
                ->findOne();

            $this->resources[$name] = $value ? unserialize($value) : [];
        }
    }
}

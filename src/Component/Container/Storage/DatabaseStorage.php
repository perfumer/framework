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
     */
    public function getParam(string $resource, string $name, $default = null)
    {
        $this->loadResource($resource);

        return isset($this->resources[$resource][$name]) ? $this->resources[$resource][$name] : $default;
    }

    /**
     * @param string $name
     * @return array
     * @throws ContainerException
     */
    public function getResource(string $name): array
    {
        $this->loadResource($name);

        return $this->resources[$name];
    }

    /**
     * @param string $resource
     * @param string $name
     * @param mixed $value
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function saveParam(string $resource, string $name, $value): void
    {
        if ($value === null) {
            return;
        }

        $param = ResourceQuery::create()
            ->filterByName($resource)
            ->filterByParameter($name)
            ->findOneOrCreate();

        $param->setValue(serialize($value));
        $param->save();
    }

    public function flush(): void
    {
        $this->resources = [];
    }

    /**
     * @param string $name
     */
    protected function loadResource(string $name): void
    {
        if (!isset($this->resources[$name])) {
            $this->resources[$name] = [];

            $params = ResourceQuery::create()
                ->filterByName($name)
                ->find();

            foreach ($params as $param) {
                $value = $param->getValue() ? unserialize($param->getValue()) : null;

                $this->resources[$name][$param->getParameter()] = $value;
            }
        }
    }
}

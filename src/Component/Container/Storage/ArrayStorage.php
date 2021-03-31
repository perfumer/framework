<?php

namespace Perfumer\Component\Container\Storage;

use Perfumer\Component\Container\Exception\ContainerException;

class ArrayStorage extends AbstractStorage
{
    /**
     * @param string $resource
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getParam(string $resource, string $name, $default = null)
    {
        if (!isset($this->resources[$resource]) || !isset($this->resources[$resource][$name])) {
            return $default;
        }

        return $this->resources[$resource][$name];
    }

    /**
     * @param string $name
     * @return array
     */
    public function getResource(string $name): array
    {
        return isset($this->resources[$name]) ? $this->resources[$name] : [];
    }

    /**
     * @param string $resource
     * @param string $name
     * @param mixed $value
     * @throws ContainerException
     */
    public function saveParam(string $resource, string $name, $value): void
    {
        throw new ContainerException('ArrayStorage does not implement "saveParam" method');
    }

    /**
     * @param array $resources
     */
    public function addResources(array $resources): void
    {
        foreach ($resources as $name => $value) {
            if (isset($this->resources[$name])) {
                $this->resources[$name] = array_merge($this->resources[$name], $value);
            } else {
                $this->resources[$name] = $value;
            }
        }
    }

    /**
     * @param string $file
     */
    public function addResourcesFromFile(string $file): void
    {
        $resources = require $file;

        $this->addResources($resources);
    }
}

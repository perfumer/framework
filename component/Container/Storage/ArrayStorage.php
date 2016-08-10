<?php

namespace Perfumer\Component\Container\Storage;

class ArrayStorage extends AbstractStorage
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
        if (!isset($this->resources[$resource]) || !isset($this->resources[$resource][$name])) {
            return $default;
        }

        return $this->resources[$resource][$name];
    }

    /**
     * @param string $name
     * @return array
     * @access public
     * @abstract
     */
    public function getResource($name)
    {
        return isset($this->resources[$name]) ? $this->resources[$name] : [];
    }

    /**
     * @param array $resources
     * @access public
     */
    public function addResources($resources)
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
     * @access public
     */
    public function addResourcesFromFile($file)
    {
        $resources = require $file;

        $this->addResources($resources);
    }
}

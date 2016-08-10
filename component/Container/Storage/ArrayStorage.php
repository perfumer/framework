<?php

namespace Perfumer\Component\Container\Storage;

class ArrayStorage extends AbstractStorage
{
    /**
     * @param string $group
     * @param string $name
     * @param mixed $default
     * @return mixed
     * @access public
     */
    public function getParam($group, $name, $default = null)
    {
        if (!isset($this->params[$group]) || !isset($this->params[$group][$name])) {
            return $default;
        }

        return $this->params[$group][$name];
    }

    /**
     * @param array $params
     * @access public
     */
    public function addParams($params)
    {
        foreach ($params as $group => $array) {
            if (isset($this->params[$group])) {
                $this->params[$group] = array_merge($this->params[$group], $array);
            } else {
                $this->params[$group] = $array;
            }
        }
    }

    /**
     * @param string $file
     * @access public
     */
    public function addParamsFromFile($file)
    {
        $params = require $file;

        $this->addParams($params);
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
        foreach ($resources as $key => $resource) {
            if (isset($this->resources[$key])) {
                $this->resources[$key] = array_merge($this->resources[$key], $resource);
            } else {
                $this->resources[$key] = $resource;
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

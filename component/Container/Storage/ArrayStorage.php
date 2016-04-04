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
     * @return $this
     */
    public function addParams($params)
    {
        $this->params = array_merge($this->params, $params);

        return $this;
    }

    /**
     * @param string $file
     * @return $this
     */
    public function addParamsFromFile($file)
    {
        $params = require $file;

        return $this->addParams($params);
    }
}

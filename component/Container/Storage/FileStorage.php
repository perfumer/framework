<?php
namespace Perfumer\Component\Container\Storage;

class FileStorage extends AbstractStorage
{
    /**
     * registerFile
     * Parsing and saving given file with parameters.
     *
     * @param string $file
     * @return void
     * @access public
     */
    public function registerFile($file)
    {
        if (is_file($file)) {
            $params = require $file;

            $this->params = array_merge($this->params, $params);
        }
    }

    /**
     * setParam
     * File storage doesn't support setting parameters.
     *
     * @param string $group
     * @param string $name
     * @param mixed $value
     * @return boolean
     * @access public
     */
    public function setParam($group, $name, $value)
    {
        return false;
    }

    /**
     * getParamGroup
     * Get array with whole group of parameters. Returns key-value array.
     *
     * @param string $group
     * @return array
     * @access public
     */
    public function getParamGroup($group)
    {
        return isset($this->params[$group]) ? $this->params[$group] : [];
    }

    /**
     * setParamGroup
     * File storage doesn't support setting parameters.
     *
     * @param string $group
     * @param array $values
     * @return boolean
     * @access public
     */
    public function setParamGroup($group, array $values)
    {
        return false;
    }

    /**
     * @param $group
     * @param array $values
     * @return bool
     */
    public function addParamGroup($group, array $values)
    {
        return false;
    }

    /**
     * @param $group
     * @param array $keys
     * @return bool
     */
    public function deleteParamGroup($group, array $keys = [])
    {
        return false;
    }
}

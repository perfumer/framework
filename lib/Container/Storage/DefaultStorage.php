<?php
namespace Perfumer\Container\Storage;

/**
 * DefaultStorage
 * Uses php-variables to store parameters.
 *
 * @package    perfumer/container
 * @category   storage
 * @author     Ilyas Makashev mehmatovec@gmail.com
 * @link       https://github.com/blumfontein/perfumer-container
 * @copyright  (c) 2014 Ilyas Makashev
 * @license    MIT
 */
class DefaultStorage extends AbstractStorage
{
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
     * setParam
     * Save one parameter.
     *
     * @param string $group
     * @param string $name
     * @param mixed $value
     * @return boolean
     * @access public
     */
    public function setParam($group, $name, $value)
    {
        if (!isset($this->params[$group]))
            $this->params[$group] = [];

        $this->params[$group][$name] = $value;

        return true;
    }

    /**
     * setParamGroup
     * Save a bunch of parameters. This method is not expected to replace whole group.
     *
     * @param string $group
     * @param array $values
     * @return boolean
     * @access public
     */
    public function setParamGroup($group, array $values)
    {
        if (!isset($this->params[$group]))
            $this->params[$group] = [];

        $this->params[$group] = array_merge($this->params[$group], $values);

        return true;
    }
}
<?php
namespace Perfumer\Container\Storage;

/**
 * FileStorage
 * Uses php-files to store parameters.
 * You must register folders and files with your parameters first.
 * All folders and files must be registered before first "getParamGroup" call.
 *
 * @package    perfumer/container
 * @category   storage
 * @author     Ilyas Makashev mehmatovec@gmail.com
 * @link       https://github.com/blumfontein/perfumer-container
 * @copyright  (c) 2014 Ilyas Makashev
 * @license    MIT
 */
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
        if (is_file($file))
        {
            $params = require $file;

            $this->params = array_merge($this->params, $params);
        }
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
}
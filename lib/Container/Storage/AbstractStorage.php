<?php
namespace Perfumer\Container\Storage;

/**
 * AbstractStorage
 * Storage is a class which manages parameters in Container. Every storage class must extend AbstractStorage.
 *
 * Currently provided storage classes:
 *  - DefaultStorage: uses simple php-variables to store parameters.
 *  - FileStorage: uses php-files to store parameters.
 *
 * Storage class must implement 3 methods:
 *  - getParamGroup: get whole group of parameters
 *  - setParam: save one parameter
 *  - setParamGroup: save a bunch of parameters (NOT replace whole group)
 *
 * AbstractStorage provides simple implementation of the method setParamGroup(), but you may want to overwrite it
 * in your storage due to performance reasons.
 *
 * @package    perfumer/container
 * @category   storage
 * @author     Ilyas Makashev mehmatovec@gmail.com
 * @link       https://github.com/blumfontein/perfumer-container
 * @copyright  (c) 2014 Ilyas Makashev
 * @license    MIT
 * @abstract
 */
abstract class AbstractStorage
{
    // Parameters array, divided to groups
    protected $params = [];

    /**
     * getParamGroup
     * Get array with whole group of parameters. Returns key-value array.
     *
     * @param string $group
     * @return array
     * @access public
     * @abstract
     */
    public abstract function getParamGroup($group);

    /**
     * setParam
     * Save one parameter
     *
     * @param string $group
     * @param string $name
     * @param mixed $value
     * @return boolean
     * @access public
     * @abstract
     */
    public abstract function setParam($group, $name, $value);

    /**
     * setParamGroup
     * Save a bunch of parameters. This method is not expected to replace whole group.
     *
     * @param string $group
     * @param array $values
     * @return boolean
     * @access public
     * @abstract
     */
    public function setParamGroup($group, array $values)
    {
        foreach ($values as $name => $value)
        {
            $this->setParam($group, $name, $value);
        }

        return true;
    }
}
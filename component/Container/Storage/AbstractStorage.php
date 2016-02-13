<?php

namespace Perfumer\Component\Container\Storage;

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
 */
abstract class AbstractStorage implements StorageInterface
{
    // Parameters array, divided to groups
    protected $params = [];

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
     * setParamGroup
     *
     * @param string $group
     * @param array $values
     * @return boolean
     * @access public
     * @abstract
     */
    public abstract function setParamGroup($group, array $values);

    public abstract function addParamGroup($group, array $values);

    public abstract function deleteParamGroup($group, array $keys = []);
}

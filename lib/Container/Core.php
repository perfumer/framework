<?php
namespace Perfumer\Container;

use Perfumer\Container\Exception\ContainerException;
use Perfumer\Container\Storage\AbstractStorage;

/**
 * Core
 * Core class of Container
 *
 * @package    perfumer/container
 * @category   core
 * @author     Ilyas Makashev mehmatovec@gmail.com
 * @link       https://github.com/blumfontein/perfumer-container
 * @copyright  (c) 2014 Ilyas Makashev
 * @license    MIT
 */
class Core
{
    // Service definitions array
    protected $service_map = [];

    // Shared service objects
    protected $services = [];

    // Storage services
    protected $storages = [];

    // Parameters array, divided to groups
    protected $params = [];

    /**
     * registerServiceMap
     * Register files containing service definitions.
     *
     * @param string $file - path to file with service definitions
     * @return void
     * @access public
     */
    public function registerServiceMap($file)
    {
        $service_map = require $file;

        $this->service_map = array_merge($this->service_map, $service_map);
    }

    /**
     * registerStorage
     * Register storage services.
     *
     * @param string $name
     * @param AbstractStorage $storage
     * @return void
     * @access public
     */
    public function registerStorage($name, AbstractStorage $storage)
    {
        $this->storages[$name] = $storage;
    }

    /**
     * getService
     * Get service instance.
     *
     * @param string $name
     * @return mixed
     * @access public
     * @uses \ReflectionClass
     */
    public function getService($name)
    {
        if (!isset($this->service_map[$name]))
            throw new ContainerException('Service "' . $name . '" is not registered.');

        $definition = $this->service_map[$name];

        if (isset($definition['shared']) && $definition['shared'] === true && isset($this->services[$name]))
            return $this->services[$name];

        if (isset($definition['alias']))
            return $this->getService($definition['alias']);

        $arguments = [];

        if (isset($definition['arguments']))
            $arguments = $this->resolveArrayOfArguments($definition['arguments']);

        if (isset($definition['static_init']))
        {
            $service_class = call_user_func_array([$definition['class'], $definition['static_init']], $arguments);

            if ($service_class === false)
                throw new ContainerException('Class "' . $definition['class'] . '" for service "' . $name . '" was not found.');
        }
        else
        {
            try
            {
                $reflection_class = new \ReflectionClass($definition['class']);
            }
            catch (\ReflectionException $e)
            {
                throw new ContainerException('Class "' . $definition['class'] . '" for service "' . $name . '" was not found.');
            }

            $service_class = $reflection_class->newInstanceArgs($arguments);
        }

        if (isset($definition['after']) && is_callable($definition['after']))
        {
            $callable = $definition['after'];
            $callable($this, $service_class);
        }

        if (isset($definition['shared']) && $definition['shared'] === true)
            $this->services[$name] = $service_class;

        return $service_class;
    }

    /**
     * resolveArrayOfArguments
     * Getting array of values which replaced placeholders in the array of arguments in the service definition.
     *
     * @param array $array
     * @return array
     * @access protected
     * @throws ContainerException
     */
    protected function resolveArrayOfArguments($array)
    {
        $arguments = [];

        foreach ($array as $key => $value)
        {
            if ($value === 'container')
            {
                $arguments[$key] = $this;
            }
            elseif (is_array($value))
            {
                $arguments[$key] = $this->resolveArrayOfArguments($value);
            }
            else
            {
                $id = $value[0];

                switch ($id)
                {
                    case '#':
                        $arguments[$key] = $this->getService(substr($value, 1));
                        break;
                    case '@':
                        $arguments[$key] = $this->getParam(substr($value, 1));
                        break;
                    default:
                        throw new ContainerException('Argument "' . $value . '" must begin either with char "#" (for services), or "@" (for parameters).');
                        break;
                }
            }
        }

        return $arguments;
    }

    /**
     * getParamGroup
     * Get array with whole group of parameters.
     *
     * @param string $group
     * @return array
     * @access public
     */
    public function getParamGroup($group)
    {
        if (!isset($this->params[$group]))
            $this->loadGroup($group);

        if (!isset($this->params[$group]))
            $this->params[$group] = [];

        return $this->params[$group];
    }

    /**
     * getParam
     * Get parameter by key string
     *
     * @example getParam('db.name') -> mixed value
     * @example getParam('db.') -> mixed value
     * @example getParam('name') -> exception
     * @param string $key
     * @return mixed
     * @access public
     */
    public function getParam($key)
    {
        list($group, $name) = $this->extractParamKey($key);

        if (!isset($this->params[$group]))
            $this->loadGroup($group);

        if (!isset($this->params[$group]))
            $this->params[$group] = [];

        return isset($this->params[$group][$name]) ? $this->params[$group][$name] : null;
    }

    /**
     * loadGroup
     * Load group of parameters from storage to Container
     *
     * @param string $group
     * @return void
     * @access protected
     */
    protected function loadGroup($group)
    {
        foreach ($this->storages as $storage)
        {
            $param_group = $storage->getParamGroup($group);

            if ($param_group)
            {
                $this->params[$group] = $param_group;
                return;
            }
        }
    }

    /**
     * setParamGroup
     * Save a bunch of parameters.
     *
     * @param string $group
     * @param array $values
     * @param string $storage - to which storage save the group
     * @return boolean
     * @access public
     */
    public function setParamGroup($group, array $values, $storage = 'default')
    {
        $saved = $this->storages[$storage]->setParamGroup($group, $values);

        if ($saved && isset($this->params[$group]))
            $this->params[$group] = $values;

        return $saved;
    }

    public function addParamGroup($group, array $values, $storage = 'default')
    {
        $saved = $this->storages[$storage]->addParamGroup($group, $values);

        if ($saved && isset($this->params[$group]))
            $this->params[$group] = array_merge($this->params[$group], $values);

        return $saved;
    }

    public function deleteParamGroup($group, array $keys = [], $storage = 'default')
    {
        $deleted = $this->storages[$storage]->deleteParamGroup($group, $keys);

        if ($deleted && isset($this->params[$group]))
        {
            if ($keys)
            {
                $this->params[$group] = $this->getService('arr')->deleteKeys($this->params[$group], $keys);
            }
            else
            {
                unset($this->params[$group]);
            }
        }

        return $deleted;
    }

    /**
     * setParam
     * Save one parameter
     *
     * @param string $key
     * @param mixed $value
     * @param string $storage - to which storage save the parameter
     * @return boolean
     * @access public
     */
    public function setParam($key, $value, $storage = 'default')
    {
        list($group, $name) = $this->extractParamKey($key);

        $saved = $this->storages[$storage]->setParam($group, $name, $value);

        if ($saved && isset($this->params[$group]))
            $this->params[$group][$name] = $value;

        return $saved;
    }

    /**
     * extractParamKey
     * Divide string containing name of the group and parameter to two parts.
     *
     * @example extractParamKey('db.name') -> ['db', 'name']
     * @example extractParamKey('db.') -> ['db', '']
     * @example extractParamKey('name') -> exception
     * @param string $key
     * @return array
     * @access protected
     * @throws ContainerException
     */
    protected function extractParamKey($key)
    {
        $parts = explode('.', $key, 2);

        if (!$parts[0])
            throw new ContainerException('Parameter group can not be empty.');

        if ($parts[1] === null)
            throw new ContainerException('Parameter name can not be null.');

        return $parts;
    }
}
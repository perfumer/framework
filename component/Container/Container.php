<?php

namespace Perfumer\Component\Container;

use Perfumer\Component\Container\Exception\ContainerException;
use Perfumer\Component\Container\Storage\DefaultStorage;
use Perfumer\Component\Container\Storage\FileStorage;
use Perfumer\Component\Container\Storage\StorageInterface;
use Perfumer\Helper\Arr;

class Container
{
    /**
     * @var array
     * Service definitions array
     */
    protected $service_map = [];

    /**
     * @var array
     * Shared service objects
     */
    protected $services = [];

    /**
     * @var array
     * Storage services
     */
    protected $storages = [];

    /**
     * @var array
     * Parameters array, divided to groups
     */
    protected $params = [];

    /**
     * Container constructor.
     */
    public function __construct()
    {
        $this->storages['default'] = new DefaultStorage();
        $this->storages['file'] = new FileStorage();
    }

    /**
     * @return DefaultStorage
     */
    public function getDefaultStorage()
    {
        return $this->storages['default'];
    }

    /**
     * @return FileStorage
     */
    public function getFileStorage()
    {
        return $this->storages['file'];
    }

    /**
     * registerServiceMap
     * Register files containing service definitions.
     *
     * @param string $file
     * @return $this
     */
    public function registerServiceMap($file)
    {
        $service_map = require $file;

        $this->service_map = array_merge($this->service_map, $service_map);

        return $this;
    }

    /**
     * @param $name
     * @param $service
     * @return $this
     */
    public function registerService($name, $service)
    {
        $this->services[$name] = $service;

        return $this;
    }

    /**
     * registerStorage
     * Register storage services.
     *
     * @param $name
     * @param StorageInterface $storage
     * @return $this
     */
    public function registerStorage($name, StorageInterface $storage)
    {
        $this->storages[$name] = $storage;

        return $this;
    }

    /**
     * @param $name
     * @return $this
     */
    public function unregisterStorage($name)
    {
        unset($this->storages[$name]);

        return $this;
    }

    /**
     * getService
     * Get service instance.
     *
     * @param string $name
     * @param array $parameters
     * @return mixed
     * @access public
     * @uses \ReflectionClass
     * @throws ContainerException
     */
    public function getService($name, array $parameters = [])
    {
        // Shared services are preserved through whole request
        if (isset($this->services[$name])) {
            return $this->services[$name];
        }

        if (!isset($this->service_map[$name])) {
            throw new ContainerException('Service "' . $name . '" is not registered.');
        }

        $definition = $this->service_map[$name];

        // Alias is a link to another definition
        if (isset($definition['alias'])) {
            return $this->getService($definition['alias']);
        }

        // "Init" directive is a callable that returns instance of service
        if (isset($definition['init']) && is_callable($definition['init'])) {
            $service_class = $definition['init']($this, $parameters);
        } else {
            $arguments = [];

            // Array of arguments which are given to constructor method
            if (isset($definition['arguments'])) {
                $arguments = $this->resolveArrayOfArguments($definition['arguments']);
            }

            // Service is made by static method
            if (isset($definition['static'])) {
                $service_class = call_user_func_array([$definition['class'], $definition['static']], $arguments);

                if ($service_class === false) {
                    throw new ContainerException('Class "' . $definition['class'] . '" for service "' . $name . '" was not found.');
                }
            } else {
                // Service is made by normal constructor
                try {
                    $reflection_class = new \ReflectionClass($definition['class']);
                } catch (\ReflectionException $e) {
                    throw new ContainerException('Class "' . $definition['class'] . '" for service "' . $name . '" was not found.');
                }

                $service_class = $reflection_class->newInstanceArgs($arguments);
            }
        }

        // "After" directive is a callable that is called after instantiation of service object
        if (isset($definition['after']) && is_callable($definition['after'])) {
            $definition['after']($this, $service_class, $parameters);
        }

        // Preserve shared service
        if (isset($definition['shared']) && $definition['shared'] === true) {
            $this->services[$name] = $service_class;
        }

        return $service_class;
    }

    /**
     * resolveArrayOfArguments
     * Getting array of values which replaced placeholders in the array of arguments in the service definition.
     *
     * @param array $array
     * @return array
     * @access protected
     */
    protected function resolveArrayOfArguments($array)
    {
        $arguments = [];

        foreach ($array as $key => $value) {
            if ($value === 'container') {
                $arguments[$key] = $this;
            } elseif (is_array($value)) {
                $arguments[$key] = $this->resolveArrayOfArguments($value);
            } elseif (is_string($value) && $value && in_array($value[0], ['#', '@'])) {
                $name = substr($value, 1);

                $arguments[$key] = ($value[0] == '#') ? $this->getService($name) : $this->getParam($name);
            } else {
                $arguments[$key] = $value;
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
        if (!isset($this->params[$group])) {
            $this->loadGroup($group);
        }

        if (!isset($this->params[$group])) {
            $this->params[$group] = [];
        }

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
     * @param mixed $default
     * @return mixed
     * @access public
     */
    public function getParam($key, $default = null)
    {
        list($group, $name) = $this->extractParamKey($key);

        if (!isset($this->params[$group])) {
            $this->loadGroup($group);
        }

        if (!isset($this->params[$group])) {
            $this->params[$group] = [];
        }

        return isset($this->params[$group][$name]) ? $this->params[$group][$name] : $default;
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
        foreach ($this->storages as $storage) {
            $param_group = $storage->getParamGroup($group);

            if ($param_group) {
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

        if ($saved && isset($this->params[$group])) {
            $this->params[$group] = $values;
        }

        return $saved;
    }

    /**
     * @param $group
     * @param array $values
     * @param string $storage
     * @return mixed
     */
    public function addParamGroup($group, array $values, $storage = 'default')
    {
        $saved = $this->storages[$storage]->addParamGroup($group, $values);

        if ($saved && isset($this->params[$group])) {
            $this->params[$group] = array_merge($this->params[$group], $values);
        }

        return $saved;
    }

    /**
     * @param $group
     * @param array $keys
     * @param string $storage
     * @return mixed
     */
    public function deleteParamGroup($group, array $keys = [], $storage = 'default')
    {
        $deleted = $this->storages[$storage]->deleteParamGroup($group, $keys);

        if ($deleted && isset($this->params[$group])) {
            if ($keys) {
                $this->params[$group] = Arr::deleteKeys($this->params[$group], $keys);
            } else {
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

        if ($saved && isset($this->params[$group])) {
            $this->params[$group][$name] = $value;
        }

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

        if (!$parts[0]) {
            throw new ContainerException('Parameter group can not be empty.');
        }

        if ($parts[1] === null) {
            throw new ContainerException('Parameter name can not be null.');
        }

        return $parts;
    }
}

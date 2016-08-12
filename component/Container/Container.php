<?php

namespace Perfumer\Component\Container;

use Interop\Container\ContainerInterface;
use Perfumer\Component\Container\Exception\BundleException;
use Perfumer\Component\Container\Exception\ContainerException;
use Perfumer\Component\Container\Storage\ArrayStorage;
use Perfumer\Component\Container\Storage\AbstractStorage;

class Container implements ContainerInterface
{
    /**
     * @var array
     */
    protected $definitions = [];

    /**
     * @var array
     */
    protected $bundles = [];

    /**
     * @var array
     */
    protected $shared = [];

    /**
     * @var array
     */
    protected $storages = [];

    /**
     * Container constructor.
     */
    public function __construct()
    {
        $this->registerStorage('array', new ArrayStorage());
    }

    /**
     * get
     * Get service instance.
     *
     * @param string $name
     * @param array $parameters
     * @return mixed
     * @access public
     * @uses \ReflectionClass
     * @throws ContainerException
     */
    public function get($name, array $parameters = [])
    {
        // Shared services are preserved through whole request
        if (isset($this->shared[$name])) {
            return $this->shared[$name];
        }

        if (!$this->has($name)) {
            throw new ContainerException('No definition found for service "' . $name . '".');
        }

        $definition = $this->definitions[$name];

        // Alias is a link to another definition
        if (isset($definition['alias'])) {
            return $this->get($definition['alias']);
        }

        // "Init" directive is a function that returns instance of service
        if (isset($definition['init'])) {
            $service_class = call_user_func($definition['init'], $this, $parameters);

            if ($service_class === false) {
                throw new ContainerException('"Init" directive for service "' . $name . '" did not produced any object.');
            }
        } else {
            $arguments = [];

            // Array of arguments which are given to constructor method
            if (isset($definition['arguments'])) {
                $arguments = $this->resolveArrayOfArguments($definition['arguments'], $parameters);
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

        // "After" directive is a function that is called after instantiation of service object
        if (isset($definition['after'])) {
            call_user_func($definition['after'], $this, $service_class, $parameters);
        }

        // Preserve shared service
        if (isset($definition['shared']) && $definition['shared'] === true) {
            $this->shared[$name] = $service_class;
        }

        return $service_class;
    }

    /**
     * @param string $id
     * @return bool
     */
    public function has($id)
    {
        return isset($this->definitions[$id]);
    }

    /**
     * @param array $bundles
     * @throws BundleException
     */
    public function registerBundles(array $bundles)
    {
        foreach ($bundles as $bundle) {
            /** @var AbstractBundle $bundle */

            if (isset($this->bundles[$bundle->getName()])) {
                throw new BundleException('Bundle "' . $bundle->getName() . '" is already registered.');
            }

            $this->bundles[$bundle->getName()] = $bundle;

            foreach ($bundle->getDefinitionFiles() as $file) {
                $this->addDefinitionsFromFile($file);
            }

            $this->addDefinitions($bundle->getDefinitions());

            /** @var ArrayStorage $array_storage */
            $array_storage = $this->getStorage('array');

            foreach ($bundle->getParamFiles() as $file) {
                $array_storage->addResourcesFromFile($file);
            }

            $array_storage->addResources($bundle->getParams());

            foreach ($bundle->getResourceFiles() as $file) {
                $array_storage->addResourcesFromFile($file);
            }

            $array_storage->addResources($bundle->getResources());

            foreach ($bundle->getStorages() as $storage) {
                $this->registerStorage($storage, $this->get($storage));
            }
        }
    }

    /**
     * @param string $name
     * @param AbstractStorage $storage
     */
    public function registerStorage($name, AbstractStorage $storage)
    {
        $this->storages[$name] = $storage;
    }

    /**
     * @param string $name
     * @throws ContainerException
     */
    public function unregisterStorage($name)
    {
        if (!isset($this->storages[$name])) {
            throw new ContainerException('Storage "' . $name . '" is not registered.');
        }

        unset($this->storages[$name]);
    }

    /**
     * @param string $name
     * @return AbstractStorage
     * @throws ContainerException
     */
    public function getStorage($name)
    {
        if (!isset($this->storages[$name])) {
            throw new ContainerException('Storage "' . $name . '" is not registered.');
        }

        return $this->storages[$name];
    }

    /**
     * @param string $name
     * @return boolean
     */
    public function hasStorage($name)
    {
        return isset($this->storages[$name]);
    }

    /**
     * @param array $definitions
     * @return $this
     */
    public function addDefinitions($definitions)
    {
        $this->definitions = array_merge($this->definitions, $definitions);
    }

    /**
     * @param string $file
     * @return $this
     */
    public function addDefinitionsFromFile($file)
    {
        $definitions = require $file;

        $this->addDefinitions($definitions);
    }

    /**
     * @param string $name
     * @param mixed $service
     */
    public function registerSharedService($name, $service)
    {
        $this->shared[$name] = $service;
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     * @access public
     */
    public function getParam($key, $default = null)
    {
        list($storage, $resource, $name) = $this->extractParamKey($key);

        return $this->getStorage($storage)->getParam($resource, $name, $default);
    }

    /**
     * @param string $key
     * @return array
     * @access public
     */
    public function getResource($key)
    {
        list($storage, $name) = $this->extractResourceKey($key);

        return $this->getStorage($storage)->getResource($name);
    }

    /**
     * @param string $name
     * @param string $alias
     * @param bool $object
     * @return mixed
     * @throws BundleException
     */
    public function resolveBundleAlias($name, $alias, $object = false)
    {
        if (!isset($this->bundles[$name])) {
            throw new BundleException('Bundle "' . $name . '" is not registered.');
        }

        /** @var AbstractBundle $bundle */
        $bundle = $this->bundles[$name];

        $service_name = $bundle->resolveAlias($alias);

        if ($object) {
            return $this->get($service_name);
        }

        return $service_name;
    }

    /**
     * @param bool $preserve_order
     * @return array
     */
    public function listBundles($preserve_order = true)
    {
        $list = array_keys($this->bundles);

        if (!$preserve_order) {
            sort($list);
        }

        return $list;
    }

    /**
     * @return array
     */
    public function listStorages()
    {
        $list = array_keys($this->storages);

        sort($list);

        return $list;
    }

    /**
     * @return array
     */
    public function listSharedServices()
    {
        $list = array_keys($this->shared);

        sort($list);

        return $list;
    }

    /**
     * @return array
     */
    public function listDefinitions()
    {
        $list = array_keys($this->definitions);

        sort($list);

        return $list;
    }

    /**
     * @param array $array
     * @param array $parameters
     * @return array
     * @access protected
     */
    protected function resolveArrayOfArguments($array, $parameters = [])
    {
        $arguments = [];

        foreach ($array as $key => $value) {
            if ($value === 'container') {
                $arguments[$key] = $this;
            } elseif (is_array($value)) {
                $arguments[$key] = $this->resolveArrayOfArguments($value, $parameters);
            } elseif (is_string($value) && $value && in_array($value[0], ['#', '@', '*', '$'])) {
                $name = substr($value, 1);

                switch ($value[0]) {
                    case '#':
                        $arguments[$key] = $this->get($name);
                        break;
                    case '@':
                        $arguments[$key] = $this->getParam($name);
                        break;
                    case '*':
                        $arguments[$key] = $this->getResource($name);
                        break;
                    case '$':
                        $arguments[$key] = (isset($parameters[$name])) ? $parameters[$name] : null;
                        break;
                }
            } else {
                $arguments[$key] = $value;
            }
        }

        return $arguments;
    }

    /**
     * @param string $key
     * @return array
     * @access protected
     * @throws ContainerException
     */
    protected function extractParamKey($key)
    {
        $parts = explode('/', (string) $key, 3);

        if (!$parts[0]) {
            throw new ContainerException('Resource name in "' . $key . '" can not be empty.');
        }

        if (!isset($parts[1]) || !$parts[1] || (isset($parts[2]) && !$parts[2])) {
            throw new ContainerException('Parameter name in "' . $key . '" can not be empty.');
        }

        if (count($parts) == 2) {
            array_unshift($parts, 'array');
        }

        return $parts;
    }

    /**
     * @param string $key
     * @return array
     * @access protected
     * @throws ContainerException
     */
    protected function extractResourceKey($key)
    {
        $parts = explode('/', (string) $key, 2);

        if (!$parts[0] || (isset($parts[1]) && !$parts[1])) {
            throw new ContainerException('Resource name in "' . $key . '" can not be empty.');
        }

        if (count($parts) == 1) {
            array_unshift($parts, 'array');
        }

        return $parts;
    }
}

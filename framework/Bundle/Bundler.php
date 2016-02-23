<?php

namespace Perfumer\Framework\Bundle;

use Perfumer\Component\Container\Container;
use Perfumer\Framework\Bundle\Exception\BundleException;

class Bundler
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var array
     */

    protected $manifests = [];

    /**
     * @var array
     */
    protected $overrides = [];

    /**
     * @var array
     */
    protected $sync_subscribers = [];

    /**
     * @var array
     */
    protected $async_subscribers = [];

    /**
     * Bundler constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $bundles_file
     */
    public function importBundlesFile($bundles_file)
    {
        $manifests = require $bundles_file;

        foreach ($manifests as $manifest)
        {
            /** @var AbstractManifest $manifest */

            $this->manifests[$manifest->getName()] = $manifest;

            foreach ($manifest->getServices() as $file) {
                $this->container->registerServiceMap($file);
            }

            foreach ($manifest->getStorages() as $storage) {
                $this->container->registerStorage($storage, $this->container->get($storage));
            }

            $file_storage = $this->container->getFileStorage();

            foreach ($manifest->getParameters() as $file) {
                $file_storage->registerFile($file);
            }

            foreach ($manifest->getControllerOverrides() as $key => $value) {
                $set = $value;
                array_unshift($set, $manifest->getName());
                $this->overrides['c#' . $key] = $set;
            }

            foreach ($manifest->getTemplateOverrides() as $key => $value) {
                $this->overrides['t#' . $key] = [$manifest->getName(), $value];
            }


            foreach ($manifest->getSyncSubscribers() as $event_name => $controllers)
            {
                if (!isset($this->sync_subscribers[$event_name])) {
                    $this->sync_subscribers[$event_name] = [];
                }

                foreach ($controllers as $controller) {
                    $set = $controller;
                    array_unshift($set, $manifest->getName());
                    $this->sync_subscribers[$event_name][] = $set;
                }
            }

            foreach ($manifest->getAsyncSubscribers() as $event_name => $controllers)
            {
                if (!isset($this->async_subscribers[$event_name])) {
                    $this->async_subscribers[$event_name] = [];
                }

                foreach ($controllers as $controller) {
                    $set = $controller;
                    array_unshift($set, $manifest->getName());
                    $this->async_subscribers[$event_name][] = $set;
                }
            }
        }
    }

    /**
     * @param string $bundle
     * @param string $alias
     * @param array $parameters
     * @return mixed
     * @throws Exception\BundleException
     */
    public function getService($bundle, $alias, $parameters = [])
    {
        if (!isset($this->manifests[$bundle])) {
            throw new BundleException('Bundle "' . $bundle . '" is not found.');
        }

        /** @var AbstractManifest $manifest */

        $manifest = $this->manifests[$bundle];

        $service_name = $manifest->getAliasedService($alias);

        return $this->container->get($service_name, $parameters);
    }

    /**
     * @param string $bundle
     * @param string $url
     * @param string $action
     * @return array
     */
    public function overrideController($bundle, $url, $action)
    {
        $key = 'c#' . $bundle . '#' . $url . '#' . $action;

        if (isset($this->overrides[$key])) {
            $result = $this->overrides[$key];
        } else {
            $result = [$bundle, $url, $action];
        }

        return $result;
    }

    /**
     * @param string $bundle
     * @param string $url
     * @return array
     */
    public function overrideTemplate($bundle, $url)
    {
        $key = 't#' . $bundle . '#' . $url;

        if (isset($this->overrides[$key])) {
            $result = $this->overrides[$key];
        } else {
            $result = [$bundle, $url];
        }

        return $result;
    }

    /**
     * @param string $event_name
     * @return array
     */
    public function getSyncSubscribers($event_name)
    {
        return isset($this->sync_subscribers[$event_name]) ? $this->sync_subscribers[$event_name] : [];
    }

    /**
     * @param string $event_name
     * @return array
     */
    public function getAsyncSubscribers($event_name)
    {
        return isset($this->async_subscribers[$event_name]) ? $this->async_subscribers[$event_name] : [];
    }
}

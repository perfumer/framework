<?php

namespace Perfumer\Framework\Bundler;

use Perfumer\Component\Container\Container;

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

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function importBundlesFile($bundles_file)
    {
        $bundles = require $bundles_file;

        foreach ($bundles as $bundle)
        {
            $manifest = require $bundle;

            $this->manifests[$manifest['name']] = $manifest;

            if (isset($manifest['services']))
            {
                foreach ($manifest['services'] as $file)
                {
                    $this->container->registerServiceMap($file);
                }
            }

            if (isset($manifest['storages']))
            {
                foreach ($manifest['storages'] as $storage)
                {
                    $this->container->registerStorage($storage, $this->container->getService($storage));
                }
            }

            if (isset($manifest['parameters']))
            {
                $file_storage = $this->container->getFileStorage();

                foreach ($manifest['parameters'] as $file)
                {
                    $file_storage->registerFile($file);
                }
            }

            if (isset($manifest['overrides']))
            {
                $overrides = $manifest['overrides'];

                if (isset($overrides['controller']))
                {
                    foreach ($overrides['controller'] as $key => $value)
                    {
                        $set = $value;
                        array_unshift($set, $manifest['name']);
                        $this->overrides['c#' . $key] = $set;
                    }
                }

                if (isset($overrides['template']))
                {
                    foreach ($overrides['template'] as $key => $value)
                        $this->overrides['t#' . $key] = [$manifest['name'], $value];
                }
            }

            if (isset($manifest['subscribers']))
            {
                $subscribers = $manifest['subscribers'];

                if (isset($subscribers['sync']))
                {
                    foreach ($subscribers['sync'] as $event_name => $controllers)
                    {
                        if (!isset($this->sync_subscribers[$event_name]))
                            $this->sync_subscribers[$event_name] = [];

                        foreach ($controllers as $controller)
                        {
                            $set = $controller;
                            array_unshift($set, $manifest['name']);
                            $this->sync_subscribers[$event_name][] = $set;
                        }
                    }
                }

                if (isset($subscribers['async']))
                {
                    foreach ($subscribers['async'] as $event_name => $controllers)
                    {
                        if (!isset($this->async_subscribers[$event_name]))
                            $this->async_subscribers[$event_name] = [];

                        foreach ($controllers as $controller)
                        {
                            $set = $controller;
                            array_unshift($set, $manifest['name']);
                            $this->async_subscribers[$event_name][] = $set;
                        }
                    }
                }
            }
        }
    }

    public function getService($bundle, $service)
    {
        $service_name = $this->manifests[$bundle]['aliases'][$service];

        return $this->container->getService($service_name);
    }

    public function overrideController($bundle, $url, $action)
    {
        $key = 'c#' . $bundle . '#' . $url . '#' . $action;

        if (isset($this->overrides[$key]))
        {
            $result = $this->overrides[$key];
        }
        else
        {
            $result = [$bundle, $url, $action];
        }

        return $result;
    }

    public function overrideTemplate($bundle, $url)
    {
        $key = 't#' . $bundle . '#' . $url;

        if (isset($this->overrides[$key]))
        {
            $result = $this->overrides[$key];
        }
        else
        {
            $result = [$bundle, $url];
        }

        return $result;
    }

    public function getSyncSubscribers($event_name)
    {
        return isset($this->sync_subscribers[$event_name]) ? $this->sync_subscribers[$event_name] : [];
    }

    public function getAsyncSubscribers($event_name)
    {
        return isset($this->async_subscribers[$event_name]) ? $this->async_subscribers[$event_name] : [];
    }
}
<?php

namespace Perfumer\MVC\Bundler;

use Perfumer\Component\Container\Core as Container;

class Bundler
{
    /**
     * @var Container
     */
    protected $container;

    protected $manifests = [];
    protected $overrides = [];

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

            if (isset($manifest['global_override']))
            {
                $global_override = $manifest['global_override'];

                if (isset($global_override['controller']))
                {
                    foreach ($global_override['controller'] as $key => $value)
                    {
                        $set = $value;
                        array_unshift($set, $manifest['name']);
                        $this->overrides['gc#' . $key] = $set;
                    }
                }

                if (isset($global_override['template']))
                {
                    foreach ($global_override['template'] as $key => $value)
                        $this->overrides['gt#' . $key] = [$manifest['name'], $value];
                }
            }

            if (isset($manifest['local_override']))
            {
                $local_override = $manifest['local_override'];

                if (isset($local_override['controller']))
                {
                    foreach ($local_override['controller'] as $key => $value)
                    {
                        $set = $value;
                        array_unshift($set, $manifest['name']);
                        $this->overrides['lc#' . $manifest['name'] . '#' . $key] = $set;
                    }
                }

                if (isset($local_override['template']))
                {
                    foreach ($local_override['template'] as $key => $value)
                        $this->overrides['lt#' . $manifest['name'] . '#' . $key] = [$manifest['name'], $value];
                }
            }
        }
    }

    public function getService($bundle, $service)
    {
        $service_name = $this->manifests[$bundle]['aliases'][$service];

        return $this->container->getService($service_name);
    }

    public function overrideController($bundle, $url, $action, $context_bundle = null)
    {
        $key = '#' . $bundle . '#' . $url . '#' . $action;

        if ($context_bundle !== null && isset($this->overrides['lc#' . $context_bundle . $key]))
        {
            $result = $this->overrides['lc#' . $context_bundle . $key];
        }
        elseif (isset($this->overrides['gc' . $key]))
        {
            $result = $this->overrides['gc' . $key];
        }
        else
        {
            $result = [$bundle, $url, $action];
        }

        return $result;
    }

    public function overrideTemplate($bundle, $url, $context_bundle = null)
    {
        $key = '#' . $bundle . '#' . $url;

        if ($context_bundle !== null && isset($this->overrides['lt#' . $context_bundle . $key]))
        {
            $result = $this->overrides['lt#' . $context_bundle . $key];
        }
        elseif (isset($this->overrides['gt' . $key]))
        {
            $result = $this->overrides['gt' . $key];
        }
        else
        {
            $result = [$bundle, $url];
        }

        return $result;
    }
}
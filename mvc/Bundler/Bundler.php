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
                        $this->overrides['gc#' . $key] = $value;
                }
            }

            if (isset($manifest['local_override']))
            {
                $local_override = $manifest['local_override'];

                if (isset($local_override['controller']))
                {
                    foreach ($local_override['controller'] as $key => $value)
                        $this->overrides['lc#' . $manifest['name'] . '#' . $key] = $value;
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
            $result = $this->overrides['lc#' . $context_bundle . $key];
        }
        else
        {
            $result = [$bundle, $url, $action];
        }

        return $result;
    }
}
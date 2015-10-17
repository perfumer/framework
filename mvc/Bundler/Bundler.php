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
        }
    }

    public function getService($bundle, $service)
    {
        $service_name = $this->manifests[$bundle]['aliases'][$service];

        return $this->container->getService($service_name);
    }
}
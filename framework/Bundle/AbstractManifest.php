<?php

namespace Perfumer\Framework\Bundle;

use Perfumer\Framework\Bundle\Exception\BundleException;

abstract class AbstractManifest
{
    abstract public function getName();

    abstract public function getDescription();

    /**
     * @return array
     */
    public function getServices()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getServiceFiles()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getStorages()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getParameterFiles()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getControllerOverrides()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getTemplateOverrides()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getSyncSubscribers()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getAsyncSubscribers()
    {
        return [];
    }

    /**
     * @param string $name
     * @return string
     * @throws BundleException
     */
    public function getAliasedService($name)
    {
        $aliases = $this->getAliases();

        if (!isset($aliases[$name])) {
            throw new BundleException('Service alias "' . $name . '" not found in manifest "' . $this->getName() . '"');
        }

        return $aliases[$name];
    }
}

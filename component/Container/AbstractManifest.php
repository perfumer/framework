<?php

namespace Perfumer\Component\Container;

use Perfumer\Component\Container\Exception\BundleException;

abstract class AbstractManifest
{
    abstract public function getName();

    abstract public function getDescription();

    /**
     * @return array
     */
    public function getDefinitions()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getDefinitionFiles()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getParamFiles()
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
    public function getConfigurators()
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
    public function getResources()
    {
        return [];
    }

    /**
     * @param string $alias
     * @return string
     * @throws BundleException
     */
    public function resolveAlias($alias)
    {
        $aliases = $this->getAliases();

        if (!isset($aliases[$alias])) {
            throw new BundleException('Service alias "' . $alias . '" not found in manifest "' . $this->getName() . '"');
        }

        return $aliases[$alias];
    }
}

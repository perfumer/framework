<?php

namespace Perfumer\Component\Container;

use Perfumer\Component\Container\Exception\ContainerException;

abstract class AbstractBundle
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
    public function getStorages()
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
     * @return array
     */
    public function getResourceFiles()
    {
        return [];
    }

    /**
     * @param string $alias
     * @return string
     * @throws ContainerException
     */
    public function resolveAlias($alias)
    {
        $aliases = $this->getAliases();

        if (!isset($aliases[$alias])) {
            throw new ContainerException('Service alias "' . $alias . '" is not found for the bundle "' . $this->getName() . '"');
        }

        return $aliases[$alias];
    }
}

<?php

namespace Perfumer\Component\Container;

abstract class AbstractConfigurator
{
    /**
     * @return string
     */
    abstract public function getName();

    /**
     * @return array
     */
    public function getResourceKeys()
    {
        return [$this->getName()];
    }

    /**
     * @param array $resources
     */
    abstract public function configure(array $resources = []);
}
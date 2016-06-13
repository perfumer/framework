<?php

namespace Perfumer\Component\Container;

abstract class AbstractConfigurator
{
    abstract public function getName();

    abstract public function configure(array $resources = []);

    public function getResourceKeys()
    {
        return [$this->getName()];
    }
}
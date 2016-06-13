<?php

namespace Perfumer\Component\Container;

abstract class AbstractConfigurator
{
    abstract public function configure(array $resources = []);
}
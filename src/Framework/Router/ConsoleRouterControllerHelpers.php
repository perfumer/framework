<?php

namespace Perfumer\Framework\Router;

trait ConsoleRouterControllerHelpers
{
    protected function o($name = null, $alias = null, $default = null)
    {
        $router = $this->getRouter();

        return $name === null ? $router->getOptions() : $router->getOption($name, $alias, $default);
    }

    protected function a($index = null, $default = null)
    {
        $router = $this->getRouter();

        return $index === null ? $router->getArguments() : $router->getArgument($index, $default);
    }
}
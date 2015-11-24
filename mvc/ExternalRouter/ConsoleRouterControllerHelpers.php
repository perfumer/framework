<?php

namespace Perfumer\MVC\ExternalRouter;

trait ConsoleRouterControllerHelpers
{
    protected function o($name = null, $alias = null, $default = null)
    {
        $router = $this->getExternalRouter();

        return $name === null ? $router->getOptions() : $router->getOption($name, $alias, $default);
    }

    protected function a($index = null, $default = null)
    {
        $router = $this->getExternalRouter();

        return $index === null ? $router->getArguments() : $router->getArgument($index, $default);
    }
}
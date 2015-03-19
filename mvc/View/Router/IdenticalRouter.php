<?php

namespace Perfumer\MVC\View\Router;

class IdenticalRouter implements RouterInterface
{
    public function dispatch($template)
    {
        return $template;
    }
}
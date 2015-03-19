<?php

namespace Perfumer\MVC\View\Router;

class IdenticalRouter
{
    public function dispatch($template)
    {
        return $template;
    }
}
<?php

namespace Perfumer\MVC\InternalRouter;

interface RouterInterface
{
    public function dispatch($url, $action, $args = []);
}
<?php

namespace Perfumer\Framework\BundleRouter;

class ConsoleRouter implements RouterInterface
{
    public function dispatch()
    {
        return $_SERVER['argv'][1];
    }
}
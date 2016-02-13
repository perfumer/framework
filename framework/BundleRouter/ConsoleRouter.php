<?php

namespace Perfumer\Framework\BundleRouter;

class ConsoleRouter implements RouterInterface
{
    protected $bundles = [];

    public function __construct($bundles = [])
    {
        $this->bundles = $bundles;
    }

    public function dispatch()
    {
        $bundle = $_SERVER['argv'][1];

        foreach ($this->bundles as $route)
        {
            if ($route['domain'] === $_SERVER['argv'][1])
            {
                $bundle = $route['bundle'];
                break;
            }
        }

        return $bundle;
    }
}

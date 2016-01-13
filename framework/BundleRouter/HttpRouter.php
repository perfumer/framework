<?php

namespace Perfumer\Framework\BundleRouter;

class HttpRouter implements RouterInterface
{
    protected $bundles = [];

    public function __construct($bundles = [])
    {
        $this->bundles = $bundles;
    }

    public function dispatch()
    {
        $bundle = null;

        foreach ($this->bundles as $route)
        {
            if ($route['domain'] === $_SERVER['SERVER_NAME'])
            {
                $bundle = $route['bundle'];
                break;
            }
        }

        return $bundle;
    }
}
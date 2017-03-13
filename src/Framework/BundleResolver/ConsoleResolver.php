<?php

namespace Perfumer\Framework\BundleResolver;

class ConsoleResolver implements ResolverInterface
{
    /**
     * @var array
     */
    protected $bundles = [];

    /**
     * ConsoleResolver constructor.
     * @param array $bundles
     */
    public function __construct($bundles = [])
    {
        $this->bundles = $bundles;
    }

    /**
     * @return string
     */
    public function dispatch()
    {
        $bundle = $_SERVER['argv'][1];

        foreach ($this->bundles as $route) {
            if ($route['domain'] === $_SERVER['argv'][1]) {
                $bundle = $route['bundle'];
                break;
            }
        }

        return $bundle;
    }
}

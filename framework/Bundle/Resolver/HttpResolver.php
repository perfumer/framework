<?php

namespace Perfumer\Framework\Bundle\Resolver;

class HttpResolver implements ResolverInterface
{
    /**
     * @var array
     */
    protected $bundles = [];

    /**
     * HttpResolver constructor.
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
        $bundle = null;

        foreach ($this->bundles as $route) {
            if ($route['domain'] === $_SERVER['SERVER_NAME']) {
                $bundle = $route['bundle'];
                break;
            }
        }

        return $bundle;
    }
}

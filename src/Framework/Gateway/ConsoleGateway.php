<?php

namespace Perfumer\Framework\Gateway;

class ConsoleGateway implements GatewayInterface
{
    /**
     * @var array
     */
    protected $bundles = [];

    /**
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

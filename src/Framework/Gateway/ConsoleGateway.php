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
     * @throws GatewayException
     */
    public function dispatch(): string
    {
        $bundle = $_SERVER['argv'][1];

        foreach ($this->bundles as $route) {
            if ($route['domain'] === $_SERVER['argv'][1]) {
                $bundle = $route['bundle'];
                break;
            }
        }

        if ($bundle === null) {
            throw new GatewayException("Console gateway could not determine bundle.");
        }

        return $bundle;
    }
}

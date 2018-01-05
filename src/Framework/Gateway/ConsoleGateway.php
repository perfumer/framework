<?php

namespace Perfumer\Framework\Gateway;

class ConsoleGateway implements GatewayInterface
{
    /**
     * @var array
     */
    protected $bundles = [];

    /**
     * @var bool
     */
    protected $debug;

    /**
     * @param array $bundles
     * @param array $options
     */
    public function __construct($bundles = [], $options = [])
    {
        $this->bundles = $bundles;
        $this->debug = $options['debug'] ?? false;
    }

    /**
     * @return string
     * @throws GatewayException
     */
    public function dispatch(): string
    {
        if ($this->debug && class_exists('\\Whoops\\Run')) {
            $whoops = new \Whoops\Run;
            $whoops->pushHandler(new \Whoops\Handler\PlainTextHandler());
            $whoops->register();
        }

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

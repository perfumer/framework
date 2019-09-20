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
    protected $debug = false;

    /**
     * @param array $options
     */
    public function __construct($options = [])
    {
        $this->debug = $options['debug'] ?? false;
    }

    public function addBundle($name, $domain, $prefix = null)
    {
        $this->bundles[] = [
            'name' => $name,
            'domain' => $domain,
            'prefix' => $prefix,
        ];
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

        $value = $_SERVER['argv'][1];

        foreach ($this->bundles as $bundle) {
            if ($bundle['domain'] === $_SERVER['argv'][1]) {
                $value = $bundle['name'];
                break;
            }
        }

        if ($value === null) {
            throw new GatewayException("Console gateway could not determine bundle.");
        }

        return $value;
    }
}

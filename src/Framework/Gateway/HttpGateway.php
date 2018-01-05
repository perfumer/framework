<?php

namespace Perfumer\Framework\Gateway;

class HttpGateway implements GatewayInterface
{
    /**
     * @var array
     */
    protected $bundles = [];

    /**
     * @var string
     */
    protected $prefix;

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
            $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());
            $whoops->register();
        }

        $bundle = null;

        foreach ($this->bundles as $route) {
            if ($route['domain'] === $_SERVER['SERVER_NAME']) {
                if (empty($route['prefix'])) {
                    $bundle = $route['bundle'];
                } else {
                    $prefix = $route['prefix'];

                    if (strpos($_SERVER['PATH_INFO'], $prefix) === 0) {
                        $this->prefix = $prefix;

                        $bundle = $route['bundle'];

                        $_SERVER['PATH_INFO'] = substr($_SERVER['PATH_INFO'], strlen($prefix));
                    }
                }
            }

            if ($bundle !== null) {
                break;
            }
        }

        if ($bundle === null) {
            throw new GatewayException("Http gateway could not determine bundle.");
        }

        return $bundle;
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }
}

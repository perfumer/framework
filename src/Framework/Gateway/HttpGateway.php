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

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
            $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());
            $whoops->register();
        }

        $value = null;

        foreach ($this->bundles as $bundle) {
            if ($bundle['domain'] === $_SERVER['SERVER_NAME']) {
                if (empty($bundle['prefix'])) {
                    $value = $bundle['name'];
                } else {
                    $prefix = $bundle['prefix'];

                    if (strpos($_SERVER['PATH_INFO'], $prefix) === 0) {
                        $this->prefix = $prefix;

                        $value = $bundle['name'];

                        $_SERVER['PATH_INFO'] = substr($_SERVER['PATH_INFO'], strlen($prefix));
                    }
                }
            }

            if ($value !== null) {
                break;
            }
        }

        if ($value === null) {
            throw new GatewayException("Http gateway could not determine bundle.");
        }

        return $value;
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }
}

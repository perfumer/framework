<?php

namespace Perfumer\Framework\Gateway;

class CompositeGateway implements GatewayInterface
{
    /**
     * @var HttpGateway
     */
    protected $http_gateway;

    /**
     * @var ConsoleGateway
     */
    protected $console_gateway;

    /**
     * @param HttpGateway $http_gateway
     * @param ConsoleGateway $console_gateway
     */
    public function __construct(HttpGateway $http_gateway, ConsoleGateway $console_gateway)
    {
        $this->http_gateway = $http_gateway;
        $this->console_gateway = $console_gateway;
    }

    public function dispatch()
    {
        $bundle = null;

        if (isset($_SERVER['SERVER_NAME'])) {
            $bundle = $this->http_gateway->dispatch();
        }

        if (isset($_SERVER['argv'])) {
            $bundle = $this->console_gateway->dispatch();
        }

        return $bundle;
    }
}
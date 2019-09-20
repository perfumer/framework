<?php

namespace Perfumer\Framework\Gateway;

use Perfumer\Framework\Application\Application;

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
     * @var Application
     */
    protected $application;

    /**
     * @param Application $application
     * @param HttpGateway $http_gateway
     * @param ConsoleGateway $console_gateway
     */
    public function __construct(Application $application, HttpGateway $http_gateway, ConsoleGateway $console_gateway)
    {
        $this->application = $application;
        $this->http_gateway = $http_gateway;
        $this->console_gateway = $console_gateway;

        $this->configure();
    }

    protected function configure(): void
    {
    }

    public function addBundle($name, $domain, $prefix = null, $env = null, $build_type = null, $flavor = null)
    {
        if ($env !== null && $env !== $this->application->getEnv()) {
            return;
        }

        if ($build_type !== null && $build_type !== $this->application->getBuildType()) {
            return;
        }

        if ($flavor !== null && $flavor !== $this->application->getFlavor()) {
            return;
        }

        if ($env !== Application::CLI) {
            $this->http_gateway->addBundle($name, $domain, $prefix);
        }

        if ($env !== Application::HTTP) {
            $this->console_gateway->addBundle($name, $domain, $prefix);
        }
    }

    /**
     * @return string
     * @throws GatewayException
     */
    public function dispatch(): string
    {
        $bundle = null;

        if (isset($_SERVER['SERVER_NAME'])) {
            $bundle = $this->http_gateway->dispatch();
        }

        if (isset($_SERVER['argv'])) {
            $bundle = $this->console_gateway->dispatch();
        }

        if ($bundle === null) {
            throw new GatewayException("Composite gateway could not determine bundle.");
        }

        return $bundle;
    }
}

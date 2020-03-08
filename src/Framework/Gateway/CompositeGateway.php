<?php

namespace Perfumer\Framework\Gateway;

use Perfumer\Framework\Application\Application;
use Perfumer\Framework\Proxy\Response;
use Symfony\Component\HttpFoundation\Request;

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

    /**
     * @param $name
     * @param $domain
     * @param null $prefix
     * @param null $env
     * @param null $build_type
     * @param null $flavor
     */
    public function addModule($name, $domain, $prefix = null, $env = null, $build_type = null, $flavor = null): void
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
            $this->http_gateway->addModule($name, $domain, $prefix);
        }

        if ($env !== Application::HTTP) {
            $this->console_gateway->addModule($name, $domain, $prefix);
        }
    }

    /**
     * @param $name
     * @param $domain
     * @param null $prefix
     * @param null $env
     * @param null $build_type
     * @param null $flavor
     * @deprecated use addModule() instead
     */
    public function addBundle($name, $domain, $prefix = null, $env = null, $build_type = null, $flavor = null): void
    {
        $this->addModule($name, $domain, $prefix, $env, $build_type, $flavor);
    }

    /**
     * @param $request mixed
     * @return string
     * @throws GatewayException
     */
    public function dispatch($request): string
    {
        $module = null;

        if ($request instanceof Request) {
            $module = $this->http_gateway->dispatch($request);
        }

        if ($request instanceof ConsoleRequest) {
            $module = $this->console_gateway->dispatch($request);
        }

        if ($module === null) {
            throw new GatewayException("Composite gateway could not determine module.");
        }

        return $module;
    }

    /**
     * @return mixed
     * @throws GatewayException
     */
    public function createRequestFromGlobals()
    {
        if (isset($_SERVER['SERVER_NAME'])) {
            return $this->http_gateway->createRequestFromGlobals();
        }

        if (isset($_SERVER['argv'])) {
            return $this->console_gateway->createRequestFromGlobals();
        }

        throw new GatewayException("Composite gateway could not create request from globals.");
    }

    /**
     * @return mixed
     * @throws GatewayException
     */
    public function createResponse()
    {
        if (isset($_SERVER['SERVER_NAME'])) {
            return $this->http_gateway->createResponse();
        }

        if (isset($_SERVER['argv'])) {
            return $this->console_gateway->createResponse();
        }

        throw new GatewayException("Composite gateway could not create response.");
    }

    /**
     * @param $response
     * @param Response $internal_response
     * @throws GatewayException
     */
    public function sendResponse($response, Response $internal_response): void
    {
        if (isset($_SERVER['SERVER_NAME'])) {
            $this->http_gateway->sendResponse($response, $internal_response);
        }

        if (isset($_SERVER['argv'])) {
            $this->console_gateway->sendResponse($response, $internal_response);
        }

        throw new GatewayException("Composite gateway could not create response.");
    }
}

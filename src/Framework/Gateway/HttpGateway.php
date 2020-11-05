<?php

namespace Perfumer\Framework\Gateway;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HttpGateway implements GatewayInterface
{
    /**
     * @var array
     */
    protected $modules = [];

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

    /**
     * @param $name
     * @param $domain
     * @param null $prefix
     */
    public function addModule($name, $domain, $prefix = null): void
    {
        $this->modules[] = [
            'name' => $name,
            'domain' => $domain,
            'prefix' => $prefix,
        ];
    }

    /**
     * @param $name
     * @param $domain
     * @param null $prefix
     * @deprecated use addModule() instead
     */
    public function addBundle($name, $domain, $prefix = null): void
    {
        $this->addModule($name, $domain, $prefix);
    }

    /**
     * @param Request $request
     * @return string
     * @throws GatewayException
     */
    public function dispatch($request): string
    {
        if ($this->debug && class_exists('\\Whoops\\Run')) {
            $whoops = new \Whoops\Run;
            $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());
            $whoops->register();
        }

        $value = null;

        foreach ($this->modules as $module) {
            if ($module['domain'] === $request->getHost() || $module['domain'] === null) {
                if (empty($module['prefix'])) {
                    $value = $module['name'];
                } else {
                    $prefix = $module['prefix'];

                    if (strpos($request->getPathInfo(), $prefix) === 0) {
                        $this->prefix = $prefix;

                        $value = $module['name'];
                    }
                }
            }

            if ($value !== null) {
                break;
            }
        }

        if ($value === null) {
            throw new GatewayException("Http gateway could not determine module.");
        }

        return $value;
    }

    /**
     * @return Request
     */
    public function createRequestFromGlobals()
    {
        return Request::createFromGlobals();
    }

    /**
     * @return Response
     */
    public function createResponse()
    {
        return new Response();
    }

    /**
     * @param Response $response
     * @param \Perfumer\Framework\Proxy\Response $internal_response
     */
    public function sendResponse($response, \Perfumer\Framework\Proxy\Response $internal_response): void
    {
        $response->setContent($internal_response->getContent())->send();
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }
}

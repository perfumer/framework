<?php

namespace Perfumer\Framework\Gateway;

use Perfumer\Framework\Proxy\Response;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\Output;

class ConsoleGateway implements GatewayInterface
{
    /**
     * @var array
     */
    protected $modules = [];

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
     * @param ConsoleRequest $request
     * @return string
     * @throws GatewayException
     */
    public function dispatch($request): string
    {
        if ($this->debug && class_exists('\\Whoops\\Run')) {
            $whoops = new \Whoops\Run;
            $whoops->pushHandler(new \Whoops\Handler\PlainTextHandler());
            $whoops->register();
        }

        $argv = $request->getArgv();

        $value = $argv[1];

        foreach ($this->modules as $module) {
            if ($module['domain'] === $argv[1]) {
                $value = $module['name'];
                break;
            }
        }

        if ($value === null) {
            throw new GatewayException("Console gateway could not determine module.");
        }

        return $value;
    }

    /**
     * @return ConsoleRequest
     */
    public function createRequestFromGlobals()
    {
        $argv = $_SERVER['argv'] ?? [];

        $request = new ConsoleRequest();
        $request->setArgv($argv);

        return $request;
    }

    /**
     * @return ConsoleOutput
     */
    public function createResponse()
    {
        return new ConsoleOutput();
    }

    /**
     * @param ConsoleOutput $response
     * @param Response $internal_response
     */
    public function sendResponse($response, Response $internal_response): void
    {
        $response->writeln($internal_response->getContent());
    }
}

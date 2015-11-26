<?php

namespace Perfumer\Framework\ExternalRouter;

use Perfumer\Framework\Proxy\Response;
use Symfony\Component\Console\Output\ConsoleOutput as ExternalResponse;

class ConsoleRouter implements RouterInterface
{
    /**
     * @var ExternalResponse
     */
    protected $response;

    protected $options = [];
    protected $arguments = [];

    public function dispatch()
    {
        $argv = $_SERVER['argv'];
        $bundle = $argv[1];
        $url = $argv[2];
        array_shift($argv);
        array_shift($argv);

        $args = \CommandLine::parseArgs($argv);

        foreach ($args as $key => $value)
        {
            if (is_string($key))
            {
                $this->options[$key] = $value;
            }
            else
            {
                $this->arguments[$key] = $value;
            }
        }

        return [$bundle, $url, 'action', []];
    }

    public function getOption($name, $alias = null, $default = null)
    {
        $return = $default;

        if (isset($this->options[$name]))
        {
            $return = $this->options[$name];
        }
        elseif (isset($this->options[$alias]))
        {
            $return = $this->options[$alias];
        }

        return $return;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getArgument($index, $default = null)
    {
        return isset($this->arguments[$index]) ? $this->arguments[$index] : $default;
    }

    public function getArguments()
    {
        return $this->arguments;
    }

    public function getExternalResponse()
    {
        if ($this->response === null)
            $this->response = new ExternalResponse();

        return $this->response;
    }

    public function sendResponse(Response $response)
    {
        $this->getExternalResponse()->writeln($response->getContent());
    }
}
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

    protected $settings = [];

    protected $options = [];
    protected $arguments = [];

    public function __construct($settings = [])
    {
        $default_settings = [
            'controller_not_found' => ['framework', 'exception/template', 'controllerNotFound']
        ];

        $this->settings = array_merge($default_settings, $settings);
    }

    public function getName()
    {
        return 'console_router';
    }

    public function dispatch()
    {
        $argv = $_SERVER['argv'];
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

        return [$url, 'action', []];
    }

    public function getControllerNotFound()
    {
        return $this->settings['controller_not_found'];
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
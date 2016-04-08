<?php

namespace Perfumer\Framework\Router;

use Perfumer\Framework\Proxy\Response;
use Symfony\Component\Console\Output\ConsoleOutput as ExternalResponse;

class ConsoleRouter implements RouterInterface
{
    /**
     * @var ExternalResponse
     */
    protected $response;

    /**
     * @var array
     */
    protected $settings = [];

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var array
     */
    protected $arguments = [];

    /**
     * ConsoleRouter constructor.
     * @param array $settings
     */
    public function __construct($settings = [])
    {
        $default_settings = [
            'allowed_actions' => ['action'],
            'not_found_attributes' => ['framework', 'exception/plain', 'controllerNotFound']
        ];

        $this->settings = array_merge($default_settings, $settings);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'console_router';
    }

    /**
     * @return array
     */
    public function getAllowedActions()
    {
        return $this->options['allowed_actions'];
    }

    /**
     * @return array
     */
    public function getNotFoundAttributes()
    {
        return $this->options['not_found_attributes'];
    }

    /**
     * @return bool
     */
    public function isHttp()
    {
        return false;
    }

    /**
     * @return array
     */
    public function dispatch()
    {
        $argv = $_SERVER['argv'];
        $resource = $argv[2];
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

        return [$resource, 'action', []];
    }

    /**
     * @return array
     */
    public function getControllerNotFound()
    {
        return $this->settings['controller_not_found'];
    }

    /**
     * @param string $name
     * @param string|null $alias
     * @param $default
     * @return mixed
     */
    public function getOption($name, $alias = null, $default = null)
    {
        $return = $default;

        if (isset($this->options[$name])) {
            $return = $this->options[$name];
        } elseif (isset($this->options[$alias])) {
            $return = $this->options[$alias];
        }

        return $return;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param $index
     * @param null $default
     * @return null
     */
    public function getArgument($index, $default = null)
    {
        return isset($this->arguments[$index]) ? $this->arguments[$index] : $default;
    }

    /**
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @return ExternalResponse
     */
    public function getExternalResponse()
    {
        if ($this->response === null) {
            $this->response = new ExternalResponse();
        }

        return $this->response;
    }

    /**
     * @param Response $response
     */
    public function sendResponse(Response $response)
    {
        $this->getExternalResponse()->writeln($response->getContent());
    }
}

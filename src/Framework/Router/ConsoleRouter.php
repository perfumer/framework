<?php

namespace Perfumer\Framework\Router;

use Symfony\Component\Console\Output\ConsoleOutput as Response;

class ConsoleRouter implements RouterInterface
{
    /**
     * @var Response
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
            'not_found_attributes' => ['framework', 'exception', 'pageNotFound']
        ];

        $this->settings = array_merge($default_settings, $settings);
    }

    /**
     * @return array
     */
    public function getAllowedActions()
    {
        return $this->settings['allowed_actions'];
    }

    /**
     * @return array
     */
    public function getNotFoundAttributes()
    {
        return $this->settings['not_found_attributes'];
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

        foreach ($args as $key => $value) {
            if (is_string($key)) {
                $this->options[$key] = $value;
            } else {
                $this->arguments[$key] = $value;
            }
        }

        return [$resource, 'action', []];
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
     * @return Response
     */
    public function getResponse()
    {
        if ($this->response === null) {
            $this->response = new Response();
        }

        return $this->response;
    }

    /**
     * @param string $content
     */
    public function sendResponse($content)
    {
        $this->getResponse()->writeln($content);
    }
}

<?php

namespace Perfumer\Framework\Router;

use Perfumer\Framework\Gateway\ConsoleRequest;

class ConsoleRouter implements RouterInterface
{
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
    public function getAllowedActions(): array
    {
        return $this->settings['allowed_actions'];
    }

    /**
     * @return array
     */
    public function getNotFoundAttributes(): array
    {
        return $this->settings['not_found_attributes'];
    }

    /**
     * @return bool
     * @deprecated Use $this->getApplication()->getEnv() instead
     */
    public function isHttp(): bool
    {
        return false;
    }

    /**
     * @param ConsoleRequest $request
     * @return array
     */
    public function dispatch($request): array
    {
        $argv = $request->getArgv();
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
}

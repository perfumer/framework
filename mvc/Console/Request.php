<?php

namespace Perfumer\MVC\Console;

class Request
{
    /**
     * @var Request
     */
    protected $initial;

    protected $url;
    protected $args;
    protected $command;
    protected $action;

    public function init($url, $action, array $args = [])
    {
        $path = explode('/', $url);

        $this->url = $url;
        $this->action = $action;
        $this->args = $args;
        $this->command = 'App\\Command\\' . implode('\\', array_map('ucfirst', $path)) . 'Command';

        return $this;
    }

    public function setInitial(Request $request)
    {
        $this->initial = $request;

        return $this;
    }

    public function getInitial()
    {
        return $this->initial;
    }

    public function isInitial()
    {
        return $this->initial !== null;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getArgs()
    {
        return $this->args;
    }

    public function getCommand()
    {
        return $this->command;
    }

    public function getAction()
    {
        return $this->action;
    }
}
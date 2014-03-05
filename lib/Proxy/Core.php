<?php

namespace Perfumer\Proxy;

use Perfumer\Container\Core as Container;
use Perfumer\Proxy\Exception\ForwardException;
use Perfumer\Proxy\Exception\ProxyException;

class Core
{
    protected $container;
    protected $request_url;
    protected $request_action;
    protected $request_args = [];
    protected $class_vars = [];

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->request_url = ($_SERVER['PATH_INFO'] !== '/') ? $_SERVER['PATH_INFO'] : $this->container->p('url.default');
        $this->request_action = strtolower($_SERVER['REQUEST_METHOD']);
    }

    public function start()
    {
        try
        {
            $response = $this->execute($this->request_url, $this->request_action, $this->request_args);
        }
        catch (ForwardException $e)
        {
            $this->start();
            return;
        }

        echo $response->sendHeaders()->getBody();
    }

    public function execute($url, $action, array $args = [])
    {
        return $this->container->s('request')->execute($url, $action, $args);
    }

    public function forward($url, $action, array $args = [])
    {
        $this->request_url = $url;
        $this->request_action = $action;
        $this->request_args = $args;

        throw new ForwardException();
    }
}
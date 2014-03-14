<?php

namespace Perfumer\Proxy;

use Perfumer\Container\Core as Container;
use Perfumer\Proxy\Exception\ForwardException;

class Core
{
    protected $container;
    protected $request_url;
    protected $request_action;
    protected $request_args = [];
    protected $http_globals = [];
    protected $http_id;
    protected $http_query = [];
    protected $http_params = [];

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->request_url = ($_SERVER['PATH_INFO'] !== '/') ? $_SERVER['PATH_INFO'] : $this->container->p('proxy.default_url');
        $this->request_action = strtolower($_SERVER['REQUEST_METHOD']);

        switch ($this->request_action)
        {
            case 'get':
                $this->http_query = $_GET;
                break;
            case 'post':
                $this->http_query = $_GET;
                $this->http_params = $_POST;
                break;
            default:
                $this->http_query = $_GET;
                parse_str(file_get_contents("php://input"), $this->http_params);
                break;
        }
    }

    public function start()
    {
        try
        {
            $response = $this->execute($this->request_url, $this->request_action, $this->request_args);
        }
        catch (ForwardException $e)
        {
            return $this->start();
        }

        return $response;
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

    public function g($name)
    {
        return $this->getGlobal($name);
    }

    public function getGlobal($name)
    {
        return isset($this->http_globals[$name]) ? $this->http_globals[$name] : null;
    }

    public function setGlobal($name, $value)
    {
        $this->http_globals[$name] = $value;

        return $this;
    }

    public function i()
    {
        return $this->http_id;
    }

    public function getId()
    {
        return $this->http_id;
    }

    public function setId($value)
    {
        $this->http_id = $value;

        return $this;
    }

    public function p($name)
    {
        return $this->getParam($name);
    }

    public function getParam($name)
    {
        return isset($this->http_params[$name]) ? $this->http_params[$name] : null;
    }

    public function setParam($name, $value)
    {
        $this->http_params[$name] = $value;

        return $this;
    }

    public function q($name)
    {
        return $this->getQuery($name);
    }

    public function getQuery($name)
    {
        return isset($this->http_query[$name]) ? $this->http_query[$name] : null;
    }

    public function setQuery($name, $value)
    {
        $this->http_query[$name] = $value;

        return $this;
    }
}
<?php

namespace Perfumer\Controller;

use Perfumer\Container\Core as Container;
use Perfumer\Controller\Exception\ExitActionException;
use Perfumer\Proxy\Request;
use Perfumer\Proxy\Response;

class CoreController
{
    protected $_container;
    protected $_proxy;
    protected $_initial;
    protected $_current;
    protected $_response;
    protected $_vars = [];

    public function __construct(Container $container, Request $request, Response $response)
    {
        $this->_container = $container;
        $this->_proxy = $container->s('proxy');
        $this->_initial = $this->_proxy->getRequestInitial();
        $this->_current = $request;
        $this->_response = $response;
    }

    public function execute()
    {
        $this->before();

        $action = $this->getCurrent()->getAction();
        $args = $this->getCurrent()->getArgs();

        $reflection_class = new \ReflectionClass($this);

        try
        {
            $reflection_class->getMethod($action)->invokeArgs($this, $args);
        }
        catch (ExitActionException $e)
        {
        }

        $this->after();

        return $this->getResponse();
    }

    protected function before()
    {
    }

    protected function after()
    {
    }

    protected function redirect($url)
    {
        $this->getResponse()->addHeader('Location', '/' . ltrim($url, '/'));
    }

    protected function getContainer()
    {
        return $this->_container;
    }

    protected function getProxy()
    {
        return $this->_proxy;
    }

    protected function getMain()
    {
        return $this->_proxy->getRequestMain();
    }

    protected function getInitial()
    {
        return $this->_initial;
    }

    protected function getCurrent()
    {
        return $this->_current;
    }

    protected function getResponse()
    {
        return $this->_response;
    }
}
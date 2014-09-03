<?php

namespace Perfumer\Controller;

use Perfumer\Container\Core as Container;
use Perfumer\Controller\Exception\ExitActionException;
use Perfumer\Proxy\Request;
use Perfumer\Proxy\Response;

class CoreController
{
    /**
     * @var \Perfumer\Container\Core
     */
    protected $_container;

    /**
     * @var \Perfumer\Proxy\Core
     */
    protected $_proxy;

    /**
     * @var \Perfumer\Proxy\Request
     */
    protected $_initial;

    /**
     * @var \Perfumer\Proxy\Request
     */
    protected $_current;

    /**
     * @var \Perfumer\Proxy\Response
     */
    protected $_response;

    /**
     * @var \Perfumer\View\Core
     */
    protected $_view;

    /**
     * @var \Perfumer\I18n\Core
     */
    protected $_i18n;

    /**
     * @var \App\Model\User
     */
    protected $_user;

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
        return $this->getProxy()->getRequestMain();
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

    protected function getView()
    {
        if ($this->_view === null)
            $this->_view = $this->getContainer()->s('view');

        return $this->_view;
    }

    protected function getI18n()
    {
        if ($this->_i18n === null)
            $this->_i18n = $this->getContainer()->s('i18n');

        return $this->_i18n;
    }

    protected function getUser()
    {
        return $this->_user;
    }
}
<?php

namespace Perfumer\Controller;

use Perfumer\Container\Core as Container;
use Perfumer\Controller\Exception\ExitActionException;
use Perfumer\Proxy\Request;
use Symfony\Component\HttpFoundation\Response;

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
    protected $_current;

    /**
     * @var \Symfony\Component\HttpFoundation\Response
     */
    protected $_response;

    /**
     * @var \Perfumer\View\Core
     */
    protected $_view;

    protected $_auth;

    /**
     * @var \Perfumer\I18n\Core
     */
    protected $_i18n;

    /**
     * @var \App\Model\User
     */
    protected $_user;

    /**
     * @var \ReflectionClass
     */
    protected $_reflection_class;

    /**
     * Default name of Auth service
     *
     * @var string
     */
    protected $_auth_service_name = 'auth';

    public function __construct(Container $container, Request $request, Response $response, \ReflectionClass $reflection_class)
    {
        $this->_container = $container;
        $this->_proxy = $container->getService('proxy');
        $this->_current = $request;
        $this->_response = $response;
        $this->_reflection_class = $reflection_class;
    }

    public function execute()
    {
        $this->before();

        $action = $this->getCurrent()->getAction();
        $args = $this->getCurrent()->getArgs();

        try
        {
            $this->_reflection_class->getMethod($action)->invokeArgs($this, $args);
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

    protected function redirect($url, $status_code = 302)
    {
        $this->getProxy()->forward('exception/page', 'redirect', [$url, $status_code]);
    }

    /**
     * Shortcut for DI Container getService() method
     *
     * @param $name
     * @return mixed
     */
    protected function s($name)
    {
        return $this->getContainer()->getService($name);
    }

    /**
     * Shortcut for Proxy getId() method
     *
     * @return mixed
     */
    protected function i()
    {
        return $this->getProxy()->getId();
    }

    /**
     * Shortcut for Proxy getQuery() method
     *
     * @param $name
     * @return mixed
     */
    protected function q($name = null, $default = null)
    {
        return $this->getProxy()->getQuery($name, $default);
    }

    /**
     * Shortcut for Proxy getArg() method
     *
     * @param $name
     * @return mixed
     */
    protected function a($name = null, $default = null)
    {
        return $this->getProxy()->getArg($name, $default);
    }

    /**
     * Shortcut for I18n translate() method
     *
     * @param $key
     * @param $placeholders
     * @return string
     */
    public function t($key, $placeholders = [])
    {
        return $this->getI18n()->translate($key, $placeholders);
    }

    /**
     * @return Container
     */
    protected function getContainer()
    {
        return $this->_container;
    }

    /**
     * @return \Perfumer\Proxy\Core
     */
    protected function getProxy()
    {
        return $this->_proxy;
    }

    /**
     * @return \Perfumer\Proxy\Request
     */
    protected function getMain()
    {
        return $this->getProxy()->getMain();
    }

    /**
     * @return \Perfumer\Proxy\Request
     */
    protected function getInitial()
    {
        $current = $this->getCurrent();

        return $current->isInitial() ? $current : $current->getInitial();
    }

    /**
     * @return \Perfumer\Proxy\Request
     */
    protected function getCurrent()
    {
        return $this->_current;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function getResponse()
    {
        return $this->_response;
    }

    /**
     * @return \Perfumer\View\Core
     */
    protected function getView()
    {
        if ($this->_view === null)
            $this->_view = $this->getContainer()->getService('view');

        return $this->_view;
    }

    protected function getAuth()
    {
        if ($this->_auth === null)
            $this->_auth = $this->getContainer()->getService($this->_auth_service_name);

        return $this->_auth;
    }

    /**
     * @return \Perfumer\I18n\Core
     */
    protected function getI18n()
    {
        if ($this->_i18n === null)
            $this->_i18n = $this->getContainer()->getService('i18n');

        return $this->_i18n;
    }

    /**
     * @return \App\Model\User
     */
    protected function getUser()
    {
        if ($this->_user === null)
            $this->_user = $this->getAuth()->getUser();

        return $this->_user;
    }
}
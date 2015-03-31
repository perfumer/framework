<?php

namespace Perfumer\MVC\Controller;

use Perfumer\Component\Container\Core as Container;
use Perfumer\MVC\Controller\Exception\ExitActionException;
use Perfumer\MVC\Proxy\Core as Proxy;
use Perfumer\MVC\Proxy\Request;
use Symfony\Component\HttpFoundation\Response;

class CoreController
{
    /**
     * @var Proxy
     */
    protected $_proxy;

    /**
     * @var Request
     */
    protected $_current;

    /**
     * @var Response
     */
    protected $_response;

    /**
     * @var \ReflectionClass
     */
    protected $_reflection_class;

    /**
     * @var \Perfumer\MVC\View\Core
     */
    protected $_view;

    protected $_auth;

    /**
     * @var \Perfumer\Component\Translator\Core
     */
    protected $_translator;

    /**
     * @var \App\Model\User
     */
    protected $_user;

    /**
     * Default name of Auth service
     *
     * @var string
     */
    protected $_auth_service_name = 'auth';

    public function __construct(Proxy $proxy, Request $request, Response $response, \ReflectionClass $reflection_class)
    {
        $this->_proxy = $proxy;
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
        $this->getProxy()->forward('exception/page', 'location', [$url, $status_code]);
    }

    protected function getInjected($key = null)
    {
        return $this->getProxy()->getInjected($key);
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
    protected function i($index = null)
    {
        return $this->getProxy()->getId($index);
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
     * Shortcut for Translator translate() method
     *
     * @param $key
     * @param $placeholders
     * @return string
     */
    public function t($key, $placeholders = [])
    {
        return $this->getTranslator()->translate($key, $placeholders);
    }

    /**
     * @return Container
     */
    protected function getContainer()
    {
        return $this->getProxy()->getInjected('_container');
    }

    /**
     * @return Proxy
     */
    protected function getProxy()
    {
        return $this->_proxy;
    }

    /**
     * @return Request
     */
    protected function getMain()
    {
        return $this->getProxy()->getMain();
    }

    /**
     * @return Request
     */
    protected function getInitial()
    {
        $current = $this->getCurrent();

        return $current->isInitial() ? $current : $current->getInitial();
    }

    /**
     * @return Request
     */
    protected function getCurrent()
    {
        return $this->_current;
    }

    /**
     * @return Response
     */
    protected function getResponse()
    {
        return $this->_response;
    }

    /**
     * @return \Perfumer\MVC\View\Core
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
     * @return \Perfumer\Component\Translator\Core
     */
    protected function getTranslator()
    {
        if ($this->_translator === null)
            $this->_translator = $this->getContainer()->getService('translator');

        return $this->_translator;
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
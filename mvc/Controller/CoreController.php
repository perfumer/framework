<?php

namespace Perfumer\MVC\Controller;

use Perfumer\Component\Container\Core as Container;
use Perfumer\MVC\Controller\Exception\ExitActionException;
use Perfumer\MVC\ExternalRouter\RouterInterface as ExternalRouter;
use Perfumer\MVC\Proxy\Core as Proxy;
use Perfumer\MVC\Proxy\Request;
use Perfumer\MVC\Proxy\Response;

class CoreController
{
    /**
     * @var Container
     */
    protected $_container;

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

    public function __construct(Container $container, Request $request, Response $response, \ReflectionClass $reflection_class)
    {
        $this->_container = $container;
        $this->_proxy = $container->getService('proxy');
        $this->_current = $request;
        $this->_response = $response;
        $this->_reflection_class = $reflection_class;
    }

    public function process()
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

    /**
     * @return array
     *
     * Array of actions available for main request
     */
    protected function getAllowedMethods()
    {
        return ['get', 'post', 'head', 'options'];
    }

    protected function execute($url, $action, array $args = [])
    {
        return $this->getProxy()->execute($this->getCurrent()->getBundle(), $url, $action, $args);
    }

    protected function forward($url, $action, array $args = [])
    {
        $this->getProxy()->forward($this->getCurrent()->getBundle(), $url, $action, $args);
    }

    protected function internalRedirect($url, $id = null, $query = [], $prefixes = [], $status_code = 302)
    {
        $this->getProxy()->forward('framework', 'redirect', 'internal', [$url, $id, $query, $prefixes, $status_code]);
    }

    protected function externalRedirect($url, $status_code = 302)
    {
        $this->getProxy()->forward('framework', 'redirect', 'external', [$url, $status_code]);
    }

    protected function addBackgroundJob($url, $action, array $args = [])
    {
        $this->getProxy()->addBackgroundJob($this->getCurrent()->getBundle(), $url, $action, $args);
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
        return $this->_container;
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
     * @return ExternalRouter
     */
    public function getExternalRouter()
    {
        return $this->_container->getService('external_router');
    }

    public function getExternalResponse()
    {
        return $this->getExternalRouter()->getExternalResponse();
    }

    /**
     * @return \Perfumer\MVC\View\View
     */
    protected function getViewInstance()
    {
        return $this->getContainer()->getService('view_factory')->getInstance();
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
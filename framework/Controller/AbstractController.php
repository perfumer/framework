<?php

namespace Perfumer\Framework\Controller;

use Perfumer\Component\Container\Container;
use Perfumer\Framework\Controller\Exception\ExitActionException;
use Perfumer\Framework\ExternalRouter\RouterInterface as ExternalRouter;
use Perfumer\Framework\Proxy\Event;
use Perfumer\Framework\Proxy\Proxy;
use Perfumer\Framework\Proxy\Request;
use Perfumer\Framework\Proxy\Response;

abstract class AbstractController
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

    public function _run()
    {
        $current = $this->getCurrent();

        if ($current->isMain() && !in_array($current->getAction(), $this->getAllowedMethods()))
            $this->actionNotFoundException();

        if (!method_exists($this, $current->getAction()))
            $this->actionNotFoundException();

        $this->before();

        $action = $current->getAction();
        $args = $current->getArgs();

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

    abstract protected function pageNotFoundException();

    abstract protected function actionNotFoundException();

    /**
     * @return array
     *
     * Array of actions available for main request
     */
    protected function getAllowedMethods()
    {
        return ['get', 'post', 'head', 'options', 'action'];
    }

    protected function execute($url, $action, array $args = [])
    {
        return $this->getProxy()->execute($this->getCurrent()->getBundle(), $url, $action, $args);
    }

    protected function forward($url, $action, array $args = [])
    {
        $this->getProxy()->forward($this->getCurrent()->getBundle(), $url, $action, $args);
    }

    protected function addBackgroundJob($url, $action, array $args = [])
    {
        $this->getProxy()->addBackgroundJob($this->getCurrent()->getBundle(), $url, $action, $args);
    }

    protected function trigger($event_name, Event $event)
    {
        $this->getProxy()->trigger($event_name, $event);
    }

    /**
     * Shortcut for DI Container getService() method
     *
     * @param string $name
     * @param array $parameters
     * @return mixed
     */
    protected function s($name, array $parameters = [])
    {
        return $this->getContainer()->getService($name, $parameters);
    }

    /**
     * Shortcut for Translator translate() method
     *
     * @param $key
     * @param $placeholders
     * @return string
     */
    protected function t($key, $placeholders = [])
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
    protected function getExternalRouter()
    {
        return $this->_container->getService('bundler')->getService($this->getCurrent()->getBundle(), 'external_router');
    }

    protected function getExternalResponse()
    {
        return $this->getExternalRouter()->getExternalResponse();
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
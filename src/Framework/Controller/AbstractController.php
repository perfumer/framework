<?php

namespace Perfumer\Framework\Controller;

use Perfumer\Component\Container\Container;
use Perfumer\Framework\Application\Application;
use Perfumer\Framework\Controller\Exception\ExitActionException;
use Perfumer\Framework\Router\RouterInterface as Router;
use Perfumer\Framework\Proxy\Event;
use Perfumer\Framework\Proxy\Exception\ProxyException;
use Perfumer\Framework\Proxy\Proxy;
use Perfumer\Framework\Proxy\Request;
use Perfumer\Framework\Proxy\Response;
use Perfumer\Framework\View\AbstractView;

abstract class AbstractController implements ControllerInterface
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

    /**
     * @var AbstractView
     */
    protected $_view;

    /**
     * @var mixed
     */
    protected $_auth;

    /**
     * AbstractController constructor.
     * @param Container $container
     * @param Request $request
     * @param \ReflectionClass $reflection_class
     */
    public function __construct(Container $container, Request $request, \ReflectionClass $reflection_class)
    {
        $this->_container = $container;
        $this->_proxy = $container->get('proxy');
        $this->_current = $request;
        $this->_response = new Response();
        $this->_reflection_class = $reflection_class;
    }

    public function _run()
    {
        $current = $this->getCurrent();

        $this->before();

        $action = $current->getAction();
        $args = $current->getArgs();

        try {
            $this->_reflection_class->getMethod($action)->invokeArgs($this, $args);
        } catch (ExitActionException $e) {
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

    protected function pageNotFoundException()
    {
        $this->getProxy()->pageNotFoundException();
    }

    /**
     * @param $resource
     * @param $action
     * @param array $args
     * @return Response
     */
    protected function execute($resource, $action, array $args = [])
    {
        return $this->getProxy()->execute($this->getCurrent()->getBundle(), $resource, $action, $args);
    }

    /**
     * @param $resource
     * @param $action
     * @param array $args
     */
    protected function forward($resource, $action, array $args = [])
    {
        $this->getProxy()->forward($this->getCurrent()->getBundle(), $resource, $action, $args);
    }

    /**
     * @param $resource
     * @param $action
     * @param array $args
     */
    protected function defer($resource, $action, array $args = [])
    {
        $this->getProxy()->defer($this->getCurrent()->getBundle(), $resource, $action, $args);
    }

    /**
     * @param $event_name
     * @param Event $event
     */
    protected function trigger($event_name, Event $event)
    {
        $this->getProxy()->trigger($event_name, $event);
    }

    /**
     * Shortcut for DI Container get() method
     *
     * @param string $name
     * @param array $parameters
     * @return mixed
     */
    protected function s($name, array $parameters = [])
    {
        return $this->getContainer()->get($name, $parameters);
    }

    /**
     * Shortcut for Translator trans() method
     *
     * @param string $key
     * @param array $parameters
     * @param string $domain
     * @param string $locale
     * @return string
     */
    protected function t($key, array $parameters = [], $domain = null, $locale = null)
    {
        return $this->getContainer()->get('translator')->trans($key, $parameters, $domain, $locale);
    }

    /**
     * @return Container
     */
    protected function getContainer()
    {
        return $this->_container;
    }

    /**
     * @return Application
     */
    protected function getApplication()
    {
        return $this->_container->get('application');
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
        return $this->getProxy()->getInitial();
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
     * @return AbstractView
     */
    protected function getView()
    {
        if ($this->_view === null) {
            $view_service_name = $this->getContainer()->resolveBundleAlias($this->getCurrent()->getBundle(), 'view');

            $this->_view = $this->getContainer()->get($view_service_name);
        }

        return $this->_view;
    }

    /**
     * @return mixed
     */
    protected function getAuth()
    {
        if ($this->_auth === null) {
            $auth_service_name = $this->getContainer()->resolveBundleAlias($this->getCurrent()->getBundle(), 'auth');

            $this->_auth = $this->getContainer()->get($auth_service_name);
        }

        return $this->_auth;
    }

    /**
     * @return Router
     */
    protected function getRouter()
    {
        return $this->getProxy()->getRouter();
    }

    protected function getExternalResponse()
    {
        return $this->getRouter()->getResponse();
    }

    /**
     * @param string $url
     * @param int $status_code
     * @throws ProxyException
     */
    protected function redirect($url, $status_code = 302)
    {
        if (!$this->getRouter()->isHttp()) {
            throw new ProxyException('Redirect is not available for non-http external routers');
        }

        $this->getProxy()->forward('framework', 'http', 'redirect', [$url, $status_code]);
    }
}

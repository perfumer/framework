<?php

namespace Perfumer\Framework\Controller;

use Perfumer\Component\Container\Container;
use Perfumer\Component\Container\Exception\NotFoundException;
use Perfumer\Framework\Application\Application;
use Perfumer\Framework\Controller\Exception\ExitActionException;
use Perfumer\Framework\Proxy\Exception\ForwardException;
use Perfumer\Framework\Router\RouterInterface as Router;
use Perfumer\Framework\Proxy\Exception\ProxyException;
use Perfumer\Framework\Proxy\Proxy;
use Perfumer\Framework\Proxy\Request;
use Perfumer\Framework\Proxy\Response;
use Perfumer\Framework\View\AbstractView;
use Psr\Container\ContainerInterface;

abstract class AbstractController implements ControllerInterface
{
    /**
     * @var ContainerInterface
     */
    private $_container;

    /**
     * @var bool
     */
    private $_is_container_reachable = true;

    /**
     * @var Application
     */
    protected $_application;

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
     * @var array
     */
    private $_components;

    /**
     * AbstractController constructor.
     * @param ContainerInterface $container
     * @param bool $is_container_reachable
     * @param Application $application
     * @param Proxy $proxy
     * @param Request $request
     * @param Response $response
     * @param array $components
     * @param \ReflectionClass $reflection_class
     */
    public function __construct(
        ContainerInterface $container,
        bool $is_container_reachable,
        Application $application,
        Proxy $proxy,
        Request $request,
        Response $response,
        array $components,
        \ReflectionClass $reflection_class
    )
    {
        $this->_container = $container;
        $this->_is_container_reachable = $is_container_reachable;
        $this->_application = $application;
        $this->_proxy = $proxy;
        $this->_current = $request;
        $this->_response = $response;
        $this->_components = $components;
        $this->_reflection_class = $reflection_class;
    }

    /**
     * @return Response
     * @throws \ReflectionException
     */
    public function _run(): Response
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

    /**
     * @param $name
     * @return mixed
     * @throws ProxyException
     */
    protected function getComponent($name)
    {
        $component_service_name = $this->_components[$name] ?? null;

        if (!$component_service_name) {
            throw new ProxyException("Component '$name' is not defined");
        }

        return $this->_container->get($component_service_name);
    }

    /**
     * @throws ProxyException
     * @throws NotFoundException
     * @throws ForwardException
     */
    protected function pageNotFoundException()
    {
        $this->getProxy()->pageNotFoundException();
    }

    /**
     * @param $resource
     * @param $action
     * @param array $args
     * @return Response
     * @throws ProxyException
     * @throws NotFoundException
     * @throws ForwardException
     */
    protected function execute($resource, $action, array $args = [])
    {
        return $this->getProxy()->execute($this->getCurrent()->getModule(), $resource, $action, $args);
    }

    /**
     * @param $resource
     * @param $action
     * @param array $args
     * @throws ForwardException
     * @throws NotFoundException
     * @throws ProxyException
     */
    protected function forward($resource, $action, array $args = [])
    {
        $this->getProxy()->forward($this->getCurrent()->getModule(), $resource, $action, $args);
    }

    /**
     * @param $resource
     * @param $action
     * @param array $args
     * @throws ForwardException
     * @throws NotFoundException
     * @throws ProxyException
     */
    protected function defer($resource, $action, array $args = [])
    {
        $this->getProxy()->defer($this->getCurrent()->getModule(), $resource, $action, $args);
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
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        return $this->_container->get($name, $parameters);
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
        return $this->_container->get('translator')->trans($key, $parameters, $domain, $locale);
    }

    /**
     * @return Container
     * @throws ProxyException
     */
    final protected function getContainer()
    {
        if (!$this->_is_container_reachable) {
            throw new ProxyException("Container is not reachable in module '{$this->getCurrent()->getModule()}'");
        }

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->_container;
    }

    /**
     * @return Application
     */
    protected function getApplication()
    {
        return $this->_application;
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
     * @throws ProxyException
     */
    protected function getView()
    {
        if ($this->_view === null) {
            $this->_view = $this->getComponent('view');
        }

        return $this->_view;
    }

    /**
     * @return mixed
     * @throws ProxyException
     */
    protected function getAuth()
    {
        if ($this->_auth === null) {
            $this->_auth = $this->getComponent('auth');
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

    protected function getExternalRequest()
    {
        return $this->getProxy()->getExternalRequest();
    }

    protected function getExternalResponse()
    {
        return $this->getProxy()->getExternalResponse();
    }

    /**
     * @param string $url
     * @param int $status_code
     * @throws ForwardException
     * @throws NotFoundException
     * @throws ProxyException
     */
    protected function redirect($url, $status_code = 302)
    {
        if (!$this->getApplication()->getEnv() !== 'http') {
            throw new ProxyException('Redirect is not available for non-http requests');
        }

        $this->getProxy()->forward('framework', 'http', 'redirect', [$url, $status_code]);
    }
}

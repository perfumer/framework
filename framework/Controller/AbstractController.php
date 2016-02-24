<?php

namespace Perfumer\Framework\Controller;

use Perfumer\Component\Container\Container;
use Perfumer\Framework\Controller\Exception\ExitActionException;
use Perfumer\Framework\ExternalRouter\RouterInterface as ExternalRouter;
use Perfumer\Framework\Proxy\Event;
use Perfumer\Framework\Proxy\Exception\ProxyException;
use Perfumer\Framework\Proxy\Proxy;
use Perfumer\Framework\Proxy\Request;
use Perfumer\Framework\Proxy\Response;

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
     * Shortcut for DI Container getService() method
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
     * Shortcut for Translator translate() method
     *
     * @param $key
     * @param $placeholders
     * @return string
     */
    protected function t($key, $placeholders = [])
    {
        return $this->getContainer()->get('translator')->translate($key, $placeholders);
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
        return $this->getProxy()->getExternalRouter();
    }

    protected function getExternalResponse()
    {
        return $this->getExternalRouter()->getExternalResponse();
    }

    /**
     * @param string $url
     * @param int $status_code
     * @throws ProxyException
     */
    protected function redirect($url, $status_code = 302)
    {
        if (!$this->getExternalRouter()->isHttp()) {
            throw new ProxyException('Redirect is not available for non-http external routers');
        }

        $this->getProxy()->forward('framework', 'http', 'redirect', [$url, $status_code]);
    }
}

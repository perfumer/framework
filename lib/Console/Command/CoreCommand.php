<?php

namespace Perfumer\Console\Command;

use Perfumer\Container\Core as Container;
use Perfumer\Console\Exception\ExitActionException;
use Perfumer\Console\Request;

class CoreCommand
{
    /**
     * @var Container
     */
    protected $_container;

    /**
     * @var \Perfumer\Console\Proxy
     */
    protected $_proxy;

    /**
     * @var Request
     */
    protected $_current;

    /**
     * @var \ReflectionClass
     */
    protected $_reflection_class;

    public function __construct(Container $container, Request $request, \ReflectionClass $reflection_class)
    {
        $this->_container = $container;
        $this->_proxy = $container->getService('console.proxy');
        $this->_current = $request;
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
    }

    protected function before()
    {
    }

    protected function after()
    {
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
     * Shortcut for Symfony Command class getHelper() method
     *
     * @param $name
     * @return mixed
     */
    protected function getHelper($name)
    {
        return $this->getContainer()->getService('console.single_application_command')->getHelper($name);
    }

    /**
     * @return Container
     */
    protected function getContainer()
    {
        return $this->_container;
    }

    /**
     * @return \Perfumer\Console\Proxy
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
     * @return \Symfony\Component\Console\Input\InputInterface
     */
    protected function getInput()
    {
        return $this->getProxy()->getInput();
    }

    /**
     * @return \Symfony\Component\Console\Output\OutputInterface
     */
    protected function getOutput()
    {
        return $this->getProxy()->getOutput();
    }
}
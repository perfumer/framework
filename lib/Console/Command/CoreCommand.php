<?php

namespace Perfumer\Console\Command;

use Perfumer\Container\Core as Container;
use Perfumer\Console\Exception\ExitActionException;
use Perfumer\Console\Request;
use Symfony\Component\Console\Output\OutputInterface;

class CoreCommand
{
    /**
     * @var \Perfumer\Container\Core
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
     * @var OutputInterface
     */
    protected $_output;

    /**
     * @var \ReflectionClass
     */
    protected $_reflection_class;

    public function __construct(Container $container, Request $request, OutputInterface $output, \ReflectionClass $reflection_class)
    {
        $this->_container = $container;
        $this->_proxy = $container->getService('console.proxy');
        $this->_current = $request;
        $this->_output = $output;
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
     * @return OutputInterface
     */
    protected function getOutput()
    {
        return $this->_output;
    }
}
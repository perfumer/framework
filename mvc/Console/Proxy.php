<?php

namespace Perfumer\MVC\Console;

use Perfumer\Component\Container\Core as Container;
use Perfumer\MVC\Console\Exception\ForwardException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Proxy
{
    /**
     * @var Container
     */
    protected $container;

    protected $request_pool = [];

    /**
     * @var Request
     */
    protected $current_initial;

    /**
     * @var Request
     */
    protected $next;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function init($url, InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $this->next = $this->container->getService('console.request')->init($url, 'action', $input->getArgument('args'));

        return $this;
    }

    public function execute($url, $action, array $args = [])
    {
        $request = $this->container->getService('console.request')->init($url, $action, $args);

        $this->executeCommand($request);
    }

    public function forward($url, $action, array $args = [])
    {
        $this->current_initial = null;

        $this->next = $this->container->getService('console.request')->init($url, $action, $args);

        throw new ForwardException();
    }

    public function getInput()
    {
        return $this->input;
    }

    public function getOutput()
    {
        return $this->output;
    }

    public function getRequestPool()
    {
        return $this->request_pool;
    }

    public function getMain()
    {
        return $this->request_pool[0];
    }

    public function start()
    {
        try
        {
            $this->executeCommand($this->next);
        }
        catch (ForwardException $e)
        {
            $this->start();
        }
    }

    protected function executeCommand(Request $request)
    {
        $this->request_pool[] = $request;

        if ($this->current_initial === null)
        {
            $this->current_initial = $request;
        }
        else
        {
            $request->setInitial($this->current_initial);
        }

        try
        {
            $reflection_class = new \ReflectionClass($request->getCommand());
        }
        catch (\ReflectionException $e)
        {
            $this->forward('exception', 'commandNotFound');
        }

        $command = $reflection_class->newInstance($this->container, $request, $reflection_class);

        return $reflection_class->getMethod('execute')->invoke($command);
    }
}
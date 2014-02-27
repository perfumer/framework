<?php

namespace Perfumer\Controller;

use Perfumer\Container\Core as Container;
use Perfumer\Controller\Exception\FilterException;
use Perfumer\Controller\Exception\HTTPException;
use Perfumer\Request;
use Perfumer\Response;

class CoreController
{
    protected $container;
    protected $request;
    protected $response;
    protected $reflection_class;

    protected $view_vars = [];
    protected $js_vars = [];
    protected $render_template = true;

    public function __construct(Container $container, Request $request, Response $response)
    {
        $this->container = $container;
        $this->request = $request;
        $this->response = $response;
        $this->reflection_class = new \ReflectionClass($this);
    }

    public function execute($method, array $args)
    {
        try
        {
            $this->before();

            if (!method_exists($this, $method))
                throw new HTTPException("Method '$method' does not exist", 404);

            $this->reflection_class->getMethod($method)->invokeArgs($this, $args);

            $this->after();
        }
        catch (FilterException $e)
        {
            $this->executeFilterExceptionHandler($e->getMessage());
        }

        if ($this->render_template)
        {
            $this->view_vars['js_vars'] = $this->js_vars;

            $body = $this->container->s('twig')->render($this->request->template, $this->view_vars);

            $this->response->setBody($body);
        }

        return $this->response;
    }

    protected function before()
    {
    }

    protected function after()
    {
    }

    protected function addViewVars(array $vars)
    {
        $this->view_vars = array_merge($this->view_vars, $vars);
    }

    protected function addJsVars(array $vars)
    {
        $this->js_vars = array_merge($this->js_vars, $vars);
    }

    protected function executeFilterExceptionHandler($handler)
    {
        $this->reflection_class->getMethod('filter' . ucfirst($handler) . 'ExceptionHandler')->invoke($this);
    }
}
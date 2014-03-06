<?php

namespace Perfumer\Controller;

use Perfumer\Container\Core as Container;
use Perfumer\Proxy\Request;
use Perfumer\Proxy\Response;

class CoreController
{
    protected $container;
    protected $proxy;
    protected $request;
    protected $response;
    protected $stock;

    protected $global_vars = [];
    protected $view_vars = [];
    protected $js_vars = [];
    protected $render_template = true;

    public function __construct(Container $container, Request $request, Response $response)
    {
        $this->container = $container;
        $this->request = $request;
        $this->response = $response;

        $this->proxy = $container->s('proxy');
        $this->stock = $container->s('stock');
    }

    public function execute()
    {
        $this->before();

        $action = $this->request->getAction();
        $args = $this->request->getArgs();

        $reflection_class = new \ReflectionClass($this);
        $reflection_class->getMethod($action)->invokeArgs($this, $args);

        $this->after();

        if ($this->render_template)
        {
            $this->global_vars['request'] = $this->request;
            $this->global_vars['response'] = $this->response;

            $this->view_vars['app'] = $this->global_vars;

            $body = $this->container->s('twig')->render($this->request->getTemplate(), $this->view_vars);

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

    protected function redirect($url)
    {
        $this->response->addHeader('Location', $url);
    }

    protected function addViewVars(array $vars)
    {
        $this->view_vars = array_merge($this->view_vars, $vars);
    }

    protected function addViewVar($name, $value)
    {
        $this->view_vars[$name] = $value;
    }

    protected function addJsVars(array $vars)
    {
        $this->js_vars = array_merge($this->js_vars, $vars);
    }

    protected function addJsVar($name, $value)
    {
        $this->js_vars[$name] = $value;
    }
}
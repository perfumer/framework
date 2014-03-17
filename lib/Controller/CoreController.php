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

    protected $view_vars = [];
    protected $app_vars = [];

    protected $template;
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
            if (!$this->template)
                $this->template = $this->request->getUrl() . '/' . $this->request->getAction() . '.twig';

            $twig = $this->container->s('twig');
            $twig->addExtension($this->container->s('twig.proxy_extension'));
            $twig->addGlobal('app', $this->app_vars);

            $body = $twig->render($this->template, $this->view_vars);

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

    protected function addAppVars(array $vars)
    {
        $this->app_vars = array_merge($this->app_vars, $vars);
    }
}
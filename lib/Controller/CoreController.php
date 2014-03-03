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
    protected $assets;
    protected $reflection_class;

    protected $filter_vars = [];
    protected $global_vars = [];
    protected $view_vars = [];
    protected $js_vars = [];
    protected $render_template = true;

    public function __construct(Container $container, Request $request, Response $response)
    {
        $this->container = $container;
        $this->request = $request;
        $this->response = $response;
        $this->assets = $this->container->s('assets');
        $this->reflection_class = new \ReflectionClass($this);

        $this->global_vars['request'] = $request;
        $this->global_vars['response'] = $response;
    }

    public function execute(array $args)
    {
        try
        {
            $this->before();
            $this->reflection_class->getMethod($this->request->getAction())->invokeArgs($this, $args);
            $this->after();
        }
        catch (FilterException $e)
        {
            $this->executeFilterExceptionHandler($e->getMessage());
        }

        if ($this->render_template)
        {
            $this->assets
                ->addCSS($this->request->getCSS())
                ->addJS($this->request->getJS());

            $this->global_vars['css'] = $this->assets->getCSS();
            $this->global_vars['js'] = $this->assets->getJS();
            $this->global_vars['vars'] = $this->js_vars;

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
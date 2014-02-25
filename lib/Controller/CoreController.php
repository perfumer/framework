<?php

namespace Perfumer\Controller;

use Blumfontein\Container\Core as Container;
use Framework\Request;
use Framework\Response;

class CoreController
{
    protected $container;
    protected $request;
    protected $response;
    protected $assets;

    protected $view_vars = [];
    protected $js_vars = [];
    protected $render_template = true;

    public function __construct(Container $container, Request $request, Response $response)
    {
        $this->container = $container;
        $this->request = $request;
        $this->response = $response;
        $this->assets = $this->container->s('assets');
    }

    public function execute($method, array $args)
    {
        $this->before();

        $reflection_class = new \ReflectionClass($this);
        $reflection_class->getMethod($method)->invokeArgs($this, $args);

        $this->after();

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
        $this->assets
            ->addCSS($this->request->css)
            ->addJS($this->request->js);

        $this->addViewVars([
            'css' => $this->assets->getCSS(),
            'js' => $this->assets->getJS()
        ]);
    }

    protected function addViewVars(array $vars)
    {
        $this->view_vars = array_merge($this->view_vars, $vars);
    }

    protected function addJsVars(array $vars)
    {
        $this->js_vars = array_merge($this->js_vars, $vars);
    }
}
<?php

namespace Perfumer\Controller;

use Perfumer\Container\Core as Container;
use Perfumer\Controller\Exception\ExitActionException;
use Perfumer\Proxy\Request;
use Perfumer\Proxy\Response;

class CoreController
{
    protected $container;
    protected $proxy;
    protected $request;
    protected $response;

    protected $framework_vars = [];
    protected $view_vars = [];
    protected $app_vars = [];

    protected $template;
    protected $render_template = true;

    public function __construct(Container $container, Request $request, Response $response)
    {
        $this->container = $container;
        $this->proxy = $container->s('proxy');
        $this->request = $request;
        $this->response = $response;
    }

    public function execute()
    {
        $this->before();

        $action = $this->request->getAction();
        $args = $this->request->getArgs();

        $reflection_class = new \ReflectionClass($this);

        try
        {
            $reflection_class->getMethod($action)->invokeArgs($this, $args);
        }
        catch (ExitActionException $e)
        {
        }

        $this->after();

        if ($this->render_template)
        {
            if (!$this->template)
                $this->template = $this->request->getUrl() . '/' . $this->request->getAction();

            $this->addAppVars([
                'initial' => $this->proxy->getRequestInitial(),
                'current' => $this->proxy->getRequestCurrent()
            ]);

            $templating = $this->container->s('templating');
            $templating->addGlobal('app', $this->app_vars);
            $templating_extension = $this->container->p('templating.extension');

            $body = $templating->render($this->template . '.' . $templating_extension, $this->view_vars);

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

    protected function addViewVar($name, $value)
    {
        $this->view_vars[$name] = $value;
    }

    protected function addViewVars(array $vars)
    {
        $this->view_vars = array_merge($this->view_vars, $vars);
    }

    protected function addAppVar($name, $value)
    {
        $this->app_vars[$name] = $value;
    }

    protected function addAppVars(array $vars)
    {
        $this->app_vars = array_merge($this->app_vars, $vars);
    }
}
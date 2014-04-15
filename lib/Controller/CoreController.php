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
    protected $view;

    public function __construct(Container $container, Request $request, Response $response)
    {
        $this->container = $container;
        $this->proxy = $container->s('proxy');
        $this->request = $request;
        $this->response = $response;
        $this->view = $container->s('view');
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

        if ($this->view->needsRendering())
        {
            $this->view->setTemplateIfNotDefined($this->request->getUrl() . '/' . $this->request->getAction());

            $this->view->addVars([
                'initial' => $this->proxy->getRequestInitial(),
                'current' => $this->request
            ], 'app');

            $body = $this->view->render();

            $this->response->setBody($body);
        }

        return $this->response;
    }

    protected function before()
    {
        $this->view->mapGroup('app');
    }

    protected function after()
    {
    }

    protected function redirect($url)
    {
        $this->response->addHeader('Location', $url);
    }
}
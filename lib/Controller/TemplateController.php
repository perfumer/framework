<?php

namespace Perfumer\Controller;

class TemplateController extends CoreController
{
    protected $view;

    protected function before()
    {
        parent::before();

        $this->view = $this->container->s('view');
        $this->view->mapGroup('app');
    }

    protected function after()
    {
        $this->view->setTemplateIfNotDefined($this->request->getUrl() . '/' . $this->request->getAction());

        $this->view->addVars([
            'initial' => $this->proxy->getRequestInitial(),
            'current' => $this->request
        ], 'app');

        $body = $this->view->render();

        $this->response->setBody($body);

        parent::after();
    }
}
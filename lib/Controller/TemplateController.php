<?php

namespace Perfumer\Controller;

class TemplateController extends CoreController
{
    protected $view;
    protected $i18n;

    protected function before()
    {
        parent::before();

        $this->view = $this->container->s('view');
        $this->view->mapGroup('app');

        $this->i18n = $this->container->s('i18n');
    }

    protected function after()
    {
        $this->view->setTemplateIfNotDefined($this->request->getUrl() . '/' . $this->request->getAction());

        $this->view->addVars([
            'main' => $this->proxy->getRequestMain(),
            'initial' => $this->proxy->getRequestInitial(),
            'current' => $this->request
        ], 'app');

        $body = $this->view->render();

        $this->response->setBody($body);

        parent::after();
    }
}
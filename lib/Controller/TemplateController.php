<?php

namespace Perfumer\Controller;

class TemplateController extends CoreController
{
    protected function before()
    {
        parent::before();

        $this->getView()->mapGroup('app')->addVar('user', $this->getUser(), 'app');
    }

    protected function after()
    {
        $current = $this->getCurrent();

        $this->getView()->setTemplateIfNotDefined($current->getUrl() . '/' . $current->getAction());

        $this->getView()->addVars([
            'main' => $this->getMain(),
            'initial' => $this->getInitial(),
            'current' => $current
        ], 'app');

        $body = $this->getView()->render();

        $this->getResponse()->setBody($body);

        parent::after();
    }
}
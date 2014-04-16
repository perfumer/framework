<?php

namespace Perfumer\Controller;

class HtmlController extends TemplateController
{
    protected function before()
    {
        parent::before();

        if (!method_exists($this, $this->request->getAction()))
            $this->proxy->forward('exception/html', 'pageNotFound');

        $this->view->mapGroup('js', 'app');
    }
}
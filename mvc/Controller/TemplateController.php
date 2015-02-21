<?php

namespace Perfumer\MVC\Controller;

class TemplateController extends CoreController
{
    protected $_rendering = true;

    protected function before()
    {
        parent::before();

        if (!method_exists($this, $this->getCurrent()->getAction()))
            $this->getProxy()->forward('exception/page', 'actionNotFound');

        $this->getView()->mapGroup('app')->addVar('user', $this->getUser(), 'app');
    }

    protected function after()
    {
        if ($this->getRendering())
        {
            $current = $this->getCurrent();

            $this->getView()->setTemplateIfNotDefined($current->getUrl() . '/' . $current->getAction());

            $this->getView()->addVars([
                'main' => $this->getMain(),
                'initial' => $this->getInitial(),
                'current' => $current
            ], 'app');

            $content = $this->getView()->render();

            $this->getResponse()->setContent($content);
        }

        parent::after();
    }

    protected function getRendering()
    {
        return $this->_rendering;
    }

    protected function setRendering($rendering)
    {
        $this->_rendering = $rendering;
    }
}
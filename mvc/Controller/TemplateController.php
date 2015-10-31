<?php

namespace Perfumer\MVC\Controller;

class TemplateController extends CoreController
{
    /**
     * @var \Perfumer\MVC\View\View
     */
    protected $_view;

    protected $_rendering = true;

    protected function before()
    {
        parent::before();

        $current = $this->getCurrent();

        if ($current->isMain() && !in_array($current->getAction(), $this->getAllowedMethods()))
            $this->getProxy()->forward('framework', 'exception/html', 'actionNotFound');

        if (!method_exists($this, $current->getAction()))
            $this->getProxy()->forward('framework', 'exception/html', 'actionNotFound');

        $this->getView()->mapGroup('app');//->addVar('user', $this->getUser(), 'app');
    }

    protected function after()
    {
        if ($this->getRendering())
        {
            $current = $this->getCurrent();

            $this->getView()->addVars([
                'main' => $this->getMain(),
                'initial' => $this->getInitial(),
                'current' => $current
            ], 'app');

            $view = $this->getView();

            if (!$view->getTemplateBundle())
                $view->setTemplateBundle($this->getCurrent()->getBundle());

            if (!$view->getTemplateUrl())
                $view->setTemplateUrl($current->getUrl() . '/' . $current->getAction());

            $content = $view->render();

            $this->getResponse()->setContent($content);
        }

        parent::after();
    }

    /**
     * @return \Perfumer\MVC\View\View
     */
    protected function getView()
    {
        if ($this->_view === null)
            $this->_view = $this->getViewInstance();

        return $this->_view;
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
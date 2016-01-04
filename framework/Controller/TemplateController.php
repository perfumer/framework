<?php

namespace Perfumer\Framework\Controller;

use Perfumer\Framework\View\TemplateView;

class TemplateController extends CoreController
{
    /**
     * @var TemplateView
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

        $this->getView()->mapGroup('app');
    }

    protected function after()
    {
        if ($this->getRendering())
        {
            $current = $this->getCurrent();

            $this->getView()->addVars([
                'bundle' => $current->getBundle(),
                'main' => $this->getMain(),
                'initial' => $this->getInitial(),
                'current' => $current
            ], 'app');

            $view = $this->getView();

            if (!$view->getTemplateBundle())
                $view->setTemplateBundle($current->getBundle());

            if (!$view->getTemplateUrl())
                $view->setTemplateUrl($current->getUrl() . '/' . $current->getAction());

            $content = $view->render();

            $this->getResponse()->setContent($content);
        }

        parent::after();
    }

    /**
     * @return TemplateView
     */
    protected function getView()
    {
        if ($this->_view === null)
            $this->_view = $this->s('view.template');

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
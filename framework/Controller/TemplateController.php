<?php

namespace Perfumer\Framework\Controller;

use Perfumer\Framework\View\TemplateView;

class TemplateController extends AbstractController
{
    protected $_rendering = true;

    protected function before()
    {
        parent::before();

        $this->getView()->mapGroup('app');
    }

    protected function after()
    {
        if ($this->getRendering()) {
            $current = $this->getCurrent();

            $this->getView()->addVars([
                'bundle' => $current->getBundle(),
                'main' => $this->getMain(),
                'initial' => $this->getInitial(),
                'current' => $current
            ], 'app');

            $view = $this->getView();

            if (!$view->getTemplate()) {
                $view->setTemplate($current->getResource() . '/' . $current->getAction());
            }

            $content = $view->render();

            $this->getResponse()->setContent($content);
        }

        parent::after();
    }

    protected function pageNotFoundException()
    {
        $this->getProxy()->forward('framework', 'exception/template', 'pageNotFound');
    }

    protected function actionNotFoundException()
    {
        $this->getProxy()->forward('framework', 'exception/template', 'actionNotFound');
    }

    /**
     * @return TemplateView
     */
    protected function getView()
    {
        return parent::getView();
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

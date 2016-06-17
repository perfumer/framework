<?php

namespace Perfumer\Framework\Controller;

use Perfumer\Framework\View\TemplateView;

class TemplateController extends ViewController
{
    protected function before()
    {
        parent::before();

        $this->getView()->addGroup('app');
    }

    protected function after()
    {
        if ($this->getRendering()) {
            $current = $this->getCurrent();

            $this->getView()->addVars([
                'bundle' => $current->getBundle(),
                'initial' => $this->getInitial(),
                'main' => $this->getMain(),
                'current' => $current
            ], 'app');

            $view = $this->getView();

            if (!$view->getTemplate()) {
                $view->setTemplate($current->getResource() . '/' . $current->getAction());
            }
        }

        parent::after();
    }

    /**
     * @return TemplateView
     */
    protected function getView()
    {
        return parent::getView();
    }
}

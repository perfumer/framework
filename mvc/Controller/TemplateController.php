<?php

namespace Perfumer\MVC\Controller;

class TemplateController extends CoreController
{
    /**
     * @var \Perfumer\MVC\View\View
     */
    protected $_view;

    protected $_template;
    protected $_rendering = true;

    protected function before()
    {
        parent::before();

        $current = $this->getCurrent();

        if ($current->isMain() && !in_array($current->getAction(), $this->getAllowedMethods()))
            $this->getProxy()->forward('exception/page', 'actionNotFound');

        if (!method_exists($this, $current->getAction()))
            $this->getProxy()->forward('exception/page', 'actionNotFound');

        $this->getView()->mapGroup('app')->addVar('user', $this->getUser(), 'app');
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

            if (!$this->getTemplate())
                $this->setTemplate($current->getUrl() . '/' . $current->getAction());

            $content = $this->getView()->render($this->_template);

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
            $this->_view = $this->getProxy()->getViewFactory()->getInstance();

        return $this->_view;
    }

    protected function getTemplate()
    {
        return $this->_template;
    }

    protected function setTemplate($template)
    {
        $this->_template = $template;
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
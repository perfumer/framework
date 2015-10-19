<?php

namespace Perfumer\MVC\Controller;

class TemplateController extends CoreController
{
    /**
     * @var \Perfumer\MVC\View\View
     */
    protected $_view;

    protected $_template_bundle;
    protected $_template;
    protected $_rendering = true;

    protected function before()
    {
        parent::before();

        $current = $this->getCurrent();

        if ($current->isMain() && !in_array($current->getAction(), $this->getAllowedMethods()))
            $this->getProxy()->forward('framework', 'exception/html', 'actionNotFound', [], ['bundle' => $current->getBundle()]);

        if (!method_exists($this, $current->getAction()))
            $this->getProxy()->forward('framework', 'exception/html', 'actionNotFound', [], ['bundle' => $current->getBundle()]);

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

            if (!$this->getTemplateBundle())
                $this->setTemplateBundle($this->getCurrent()->getBundle());

            if (!$this->getTemplate())
                $this->setTemplate($current->getUrl() . '/' . $current->getAction());

            $content = $this->getView()->render($this->_template_bundle, $this->_template);

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

    protected function getTemplateBundle()
    {
        return $this->_template_bundle;
    }

    protected function setTemplateBundle($bundle)
    {
        $this->_template_bundle = $bundle;
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
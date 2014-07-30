<?php

namespace Perfumer\Controller;

class TemplateController extends CoreController
{
    /**
     * @var \Perfumer\View\Core
     */
    protected $_view;

    /**
     * @var \Perfumer\I18n\Core
     */
    protected $_i18n;

    protected function before()
    {
        parent::before();

        $this->_view = $this->getContainer()->s('view');
        $this->_i18n = $this->getContainer()->s('i18n');

        $this->getView()->mapGroup('app');
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

    protected function getView()
    {
        return $this->_view;
    }

    protected function getI18n()
    {
        return $this->_i18n;
    }
}
<?php

namespace Perfumer\Controller\Helper;

use Perfumer\Controller\Exception\ExitActionException;

trait ContentHelper
{
    protected function contentBeforeFilter()
    {
        $this->_framework_vars['content'] = null;
    }

    protected function contentAfterFilter()
    {
        $this->view->addVar('content', $this->getContent());
    }

    protected function getContent()
    {
        return $this->_framework_vars['content'];
    }

    protected function setContent($content)
    {
        $this->_framework_vars['content'] = $content;
    }

    protected function setContentAndExit($content)
    {
        $this->setContent($content);

        throw new ExitActionException();
    }
}
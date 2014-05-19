<?php

namespace Perfumer\Controller\Helper;

use Perfumer\Controller\Exception\ExitActionException;

trait ContentHelper
{
    protected function contentBeforeFilter()
    {
        $this->_vars['content'] = null;
    }

    protected function contentAfterFilter()
    {
        $this->getView()->addVar('content', $this->getContent());
    }

    protected function getContent()
    {
        return $this->_vars['content'];
    }

    protected function setContent($content)
    {
        $this->_vars['content'] = $content;
    }

    protected function setContentAndExit($content)
    {
        $this->setContent($content);

        throw new ExitActionException();
    }
}
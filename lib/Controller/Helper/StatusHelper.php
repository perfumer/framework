<?php

namespace Perfumer\Controller\Helper;

use Perfumer\Controller\Exception\ExitActionException;

trait StatusHelper
{
    protected function statusBeforeFilter()
    {
        $this->_vars['status'] = null;
    }

    protected function statusAfterFilter()
    {
        $status = (bool) $this->getStatus();

        $this->getView()->addVar('status', $status);
    }

    protected function errorStatusAfterFilter()
    {
        if ($this->getStatus() === null)
        {
            $status = !($this->getErrorMessage() || $this->hasErrors());
            $this->setStatus($status);
        }

        $this->statusAfterFilter();
    }

    protected function getStatus()
    {
        return $this->_vars['status'];
    }

    protected function setStatus($status)
    {
        $this->_vars['status'] = (bool) $status;
    }

    protected function setStatusAndExit($status)
    {
        $this->setStatus($status);

        throw new ExitActionException();
    }
}
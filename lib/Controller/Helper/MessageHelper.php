<?php

namespace Perfumer\Controller\Helper;

use Perfumer\Controller\Exception\ExitActionException;

trait MessageHelper
{
    protected function messageBeforeFilter()
    {
        $this->_vars['message'] = null;
        $this->_vars['error_message'] = null;
        $this->_vars['success_message'] = null;
    }

    protected function messageAfterFilter()
    {
        $this->getView()->addVar('message', $this->getMessage());
    }

    protected function statusMessageAfterFilter()
    {
        $message = $this->getStatus() ? $this->getSuccessMessage() : $this->getErrorMessage();

        $this->getView()->addVar('message', $message);
    }

    protected function getMessage()
    {
        return $this->_vars['message'];
    }

    protected function setMessage($message)
    {
        $this->_vars['message'] = $message;
    }

    protected function setMessageAndExit($message)
    {
        $this->setMessage($message);

        throw new ExitActionException();
    }

    protected function getSuccessMessage()
    {
        return $this->_vars['success_message'];
    }

    protected function setSuccessMessage($message)
    {
        $this->setStatus(true);

        $this->_vars['success_message'] = $message;
    }

    protected function setSuccessMessageAndExit($message)
    {
        $this->setSuccessMessage($message);

        throw new ExitActionException();
    }

    protected function getErrorMessage()
    {
        return $this->_vars['error_message'];
    }

    protected function setErrorMessage($message)
    {
        $this->setStatus(false);

        $this->_vars['error_message'] = $message;
    }

    protected function setErrorMessageAndExit($message)
    {
        $this->setErrorMessage($message);

        throw new ExitActionException();
    }
}
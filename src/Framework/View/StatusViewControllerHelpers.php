<?php

namespace Perfumer\Framework\View;

use Perfumer\Framework\Controller\Exception\ExitActionException;

trait StatusViewControllerHelpers
{
    /**
     * @return bool
     */
    protected function getStatus()
    {
        return $this->getView()->getStatus();
    }

    /**
     * @param bool $status
     */
    protected function setStatus($status)
    {
        $this->getView()->setStatus($status);
    }

    /**
     * @param bool $status
     * @throws ExitActionException
     */
    protected function setStatusAndExit($status)
    {
        $this->getView()->setStatus($status);

        throw new ExitActionException();
    }

    /**
     * @return string
     */
    protected function getStatusCode()
    {
        return $this->getView()->getStatusCode();
    }

    /**
     * @param string $status_code
     */
    protected function setStatusCode($status_code)
    {
        $this->getView()->setStatusCode($status_code);
    }

    /**
     * @param string $status_code
     * @throws ExitActionException
     */
    protected function setStatusCodeAndExit($status_code)
    {
        $this->getView()->setStatusCode($status_code);

        throw new ExitActionException();
    }

    /**
     * @return string
     */
    protected function getMessage()
    {
        return $this->getView()->getMessage();
    }

    /**
     * @param string $message
     */
    protected function setErrorMessage($message)
    {
        $this->getView()->setErrorMessage($message);
    }

    /**
     * @param string $message
     * @throws ExitActionException
     */
    protected function setErrorMessageAndExit($message)
    {
        $this->getView()->setErrorMessage($message);

        throw new ExitActionException;
    }

    /**
     * @param string $message
     */
    protected function setSuccessMessage($message)
    {
        $this->getView()->setSuccessMessage($message);
    }

    /**
     * @param string $message
     * @throws ExitActionException
     */
    protected function setSuccessMessageAndExit($message)
    {
        $this->getView()->setSuccessMessage($message);

        throw new ExitActionException;
    }

    /**
     * @return bool
     */
    protected function hasMessage()
    {
        return $this->getView()->hasMessage();
    }

    /**
     * @return array
     */
    protected function getErrors()
    {
        return $this->getView()->getErrors();
    }

    /**
     * @param string $key
     * @return mixed
     */
    protected function getError($key)
    {
        return $this->getView()->getError($key);
    }

    /**
     * @param array $errors
     */
    protected function addErrors($errors)
    {
        $this->getView()->addErrors($errors);
    }

    /**
     * @param array $errors
     * @throws ExitActionException
     */
    protected function addErrorsAndExit($errors)
    {
        $this->getView()->addErrors($errors);

        throw new ExitActionException;
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    protected function addError($key, $value)
    {
        $this->getView()->addError($key, $value);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @throws ExitActionException
     */
    protected function addErrorAndExit($key, $value)
    {
        $this->getView()->addError($key, $value);

        throw new ExitActionException;
    }

    /**
     * @return bool
     */
    protected function hasErrors()
    {
        return $this->getView()->hasErrors();
    }

    /**
     * @param string $key
     * @return bool
     */
    protected function hasError($key)
    {
        return $this->getView()->hasError($key);
    }

    /**
     * @return mixed
     */
    protected function getContent()
    {
        return $this->getView()->getContent();
    }

    /**
     * @param mixed $content
     */
    protected function setContent($content)
    {
        $this->getView()->setContent($content);
    }

    /**
     * @param mixed $content
     * @throws ExitActionException
     */
    protected function setContentAndExit($content)
    {
        $this->getView()->setContent($content);

        throw new ExitActionException();
    }

    /**
     * @return bool mixed
     */
    protected function hasContent()
    {
        return $this->getView()->hasContent();
    }
}

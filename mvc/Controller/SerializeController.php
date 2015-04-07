<?php

namespace Perfumer\MVC\Controller;

use Perfumer\MVC\Controller\Exception\ExitActionException;

class SerializeController extends CoreController
{
    /**
     * @var \Perfumer\MVC\View\View
     */
    protected $_view;

    /*
     * Default serialize method
     */
    protected $_serializer = 'json';

    protected function before()
    {
        parent::before();

        if (!method_exists($this, $this->getCurrent()->getAction()))
            $this->getProxy()->forward('exception/' . $this->_serializer, 'actionNotFound');

        $this->getView()->addVars([
            'status' => true,
            'message' => '',
            'content' => ''
        ]);

        $this->getView()->mapGroup('errors');
    }

    protected function after()
    {
        $content = $this->getView()->serializeVars($this->_serializer);

        $this->getResponse()->setContent($content);

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

    protected function getStatus()
    {
        return $this->getView()->getVar('status');
    }

    protected function setStatus($status)
    {
        $this->getView()->addVar('status', (bool) $status);
    }

    protected function setStatusAndExit($status)
    {
        $this->setStatus($status);

        throw new ExitActionException();
    }

    protected function getMessage()
    {
        return $this->getView()->getVar('message');
    }

    protected function setErrorMessage($message)
    {
        $this->getView()->addVars([
            'status' => false,
            'message' => $message
        ]);
    }

    protected function setErrorMessageAndExit($message)
    {
        $this->setErrorMessage($message);

        throw new ExitActionException;
    }

    protected function setSuccessMessage($message)
    {
        $this->getView()->addVars([
            'status' => true,
            'message' => $message
        ]);
    }

    protected function setSuccessMessageAndExit($message)
    {
        $this->setSuccessMessage($message);

        throw new ExitActionException;
    }

    protected function hasMessage()
    {
        return $this->getView()->hasVar('message');
    }

    protected function getError($key)
    {
        return $this->getView()->getVar($key, 'errors');
    }

    protected function addError($key, $value)
    {
        $this->getView()->addVar('status', false)->addVar($key, $value, 'errors');
    }

    protected function addErrorAndExit($key, $value)
    {
        $this->addError($key, $value);

        throw new ExitActionException;
    }

    protected function hasError($key)
    {
        return $this->getView()->hasVar($key, 'errors');
    }

    protected function getErrors()
    {
        return $this->getView()->getVars('errors');
    }

    protected function addErrors($errors)
    {
        $this->getView()->addVar('status', false)->addVars($errors, 'errors');
    }

    protected function addErrorsAndExit($errors)
    {
        $this->addErrors($errors);

        throw new ExitActionException;
    }

    protected function hasErrors()
    {
        return $this->getView()->hasVars('errors');
    }

    protected function getContent()
    {
        return $this->getView()->getVar('content');
    }

    protected function setContent($content)
    {
        $this->getView()->addVar('content', $content);
    }

    protected function setContentAndExit($content)
    {
        $this->setContent($content);

        throw new ExitActionException();
    }

    protected function hasContent()
    {
        return $this->getView()->hasVar('content');
    }
}
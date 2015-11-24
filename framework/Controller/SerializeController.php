<?php

namespace Perfumer\Framework\Controller;

use Perfumer\Framework\Controller\Exception\ExitActionException;
use Perfumer\Framework\View\View;

class SerializeController extends CoreController
{
    /**
     * @var View
     */
    protected $_view;

    /*
     * Default serialize method
     */
    protected $_serializer = 'json';

    protected function before()
    {
        parent::before();

        $current = $this->getCurrent();

        if ($current->isMain() && !in_array($current->getAction(), $this->getAllowedMethods()))
            $this->getProxy()->forward('framework', 'exception/html', 'actionNotFound');

        if (!method_exists($this, $current->getAction()))
            $this->getProxy()->forward('framework', 'exception/' . $this->_serializer, 'actionNotFound');

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
     * @return View
     */
    protected function getView()
    {
        if ($this->_view === null)
            $this->_view = $this->getViewInstance();

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
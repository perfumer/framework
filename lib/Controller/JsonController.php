<?php

namespace Perfumer\Controller;

use Perfumer\Controller\Exception\ExitActionException;

class JsonController extends CoreController
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

        $this->getView()->addVars([
            'status' => false,
            'message' => '',
            'content' => ''
        ]);

        $this->getView()->mapGroup('errors');
    }

    protected function after()
    {
        $body = $this->getView()->serializeVars('json');

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

    protected function setStatus($status)
    {
        $this->getView()->addVar('status', (bool) $status);
    }

    protected function setStatusAndExit($status)
    {
        $this->setStatus($status);

        throw new ExitActionException();
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

    protected function addError($key, $value)
    {
        $this->getView()->addVar('status', false)->addVar($key, $value, 'errors');
    }

    protected function addErrorAndExit($key, $value)
    {
        $this->addError($key, $value);

        throw new ExitActionException;
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

    protected function setContent($content)
    {
        $this->getView()->addVar('content', $content);
    }

    protected function setContentAndExit($content)
    {
        $this->setContent($content);

        throw new ExitActionException();
    }
}
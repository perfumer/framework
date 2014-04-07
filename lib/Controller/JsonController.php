<?php

namespace Perfumer\Controller;

use Perfumer\Controller\Exception\ExitActionException;
use Symfony\Component\Validator\ConstraintViolationList;

class JsonController extends CoreController
{
    protected function before()
    {
        parent::before();

        if (!method_exists($this, $this->request->getAction()))
            $this->proxy->forward('exception/json', 'pageNotFound');

        $this->framework_vars = [
            'status' => null,
            'error_message' => null,
            'default_error_message' => null,
            'success_message' => null,
            'default_success_message' => null,
            'errors' => [],
            'content' => null
        ];
    }

    protected function after()
    {
        if ($this->framework_vars['status'] === null)
        {
            $status = !($this->framework_vars['error_message'] || count($this->framework_vars['errors']) > 0);
            $this->setStatus($status);
        }

        if (!$this->template)
            $this->template = 'layout/json';

        if ($this->framework_vars['status'])
            $message = $this->framework_vars['success_message'] ?: $this->framework_vars['default_success_message'];
        else
            $message = $this->framework_vars['error_message'] ?: $this->framework_vars['default_error_message'];

        $this->framework_vars['message'] = $message;

        unset($this->framework_vars['default_success_message']);
        unset($this->framework_vars['success_message']);
        unset($this->framework_vars['default_error_message']);
        unset($this->framework_vars['error_message']);

        $this->addViewVars($this->framework_vars);

        parent::after();
    }

    protected function addErrors($errors)
    {
        if ($errors instanceof ConstraintViolationList)
        {
            foreach ($errors as $error)
                $this->framework_vars['errors'][$error->getPropertyPath()] = $error->getMessage();
        }
        else
        {
            $this->framework_vars['errors'] = array_merge($this->framework_vars['errors'], $errors);
        }
    }

    protected function hasErrors()
    {
        return count($this->framework_vars['errors']) > 0;
    }

    protected function setStatus($status)
    {
        $this->framework_vars['status'] = (bool) $status;
    }

    protected function setStatusAndExit($status)
    {
        $this->setStatus($status);

        throw new ExitActionException();
    }

    protected function setDefaultSuccessMessage($message)
    {
        $this->framework_vars['default_success_message'] = $message;
    }

    protected function setSuccessMessage($message)
    {
        $this->framework_vars['status'] = true;
        $this->framework_vars['success_message'] = $message;
    }

    protected function setSuccessMessageAndExit($message)
    {
        $this->setSuccessMessage($message);

        throw new ExitActionException();
    }

    protected function setDefaultErrorMessage($message)
    {
        $this->framework_vars['default_error_message'] = $message;
    }

    protected function setErrorMessage($message)
    {
        $this->framework_vars['status'] = false;
        $this->framework_vars['success_message'] = $message;
    }

    protected function setErrorMessageAndExit($message)
    {
        $this->setErrorMessage($message);

        throw new ExitActionException();
    }

    protected function setContent($content)
    {
        $this->framework_vars['content'] = $content;
    }
}
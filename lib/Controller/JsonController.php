<?php

namespace Perfumer\Controller;

use Perfumer\Controller\Exception\ExitActionException;
use Symfony\Component\Validator\ConstraintViolationList;

class JsonController extends CoreController
{
    protected $status;
    protected $error_message;
    protected $default_error_message;
    protected $success_message;
    protected $default_success_message;
    protected $content;
    protected $errors = [];

    protected function before()
    {
        parent::before();

        if (!method_exists($this, $this->request->getAction()))
            $this->proxy->forward('exception/json', 'pageNotFound');
    }

    protected function after()
    {
        if ($this->status === null)
            $this->status = !($this->error_message || count($this->errors) > 0);

        if (!$this->template)
            $this->template = 'layout/json';

        if ($this->status)
            $message = $this->success_message ?: $this->default_success_message;
        else
            $message = $this->error_message ?: $this->default_error_message;

        $this->addViewVars([
            'status' => (int) $this->status,
            'message' => $message,
            'content' => $this->content,
            'errors' => $this->errors
        ]);

        parent::after();
    }

    protected function addErrors($errors)
    {
        if ($errors instanceof ConstraintViolationList)
        {
            foreach ($errors as $error)
                $this->errors[$error->getPropertyPath()] = $error->getMessage();
        }
        else
        {
            $this->errors = array_merge($this->errors, $errors);
        }
    }

    protected function setStatusAndExit($status)
    {
        $this->status = (bool) $status;

        throw new ExitActionException();
    }

    protected function setSuccessMessageAndExit($message)
    {
        $this->status = true;
        $this->success_message = $message;

        throw new ExitActionException();
    }

    protected function setErrorMessageAndExit($message)
    {
        $this->status = false;
        $this->error_message = $message;

        throw new ExitActionException();
    }
}
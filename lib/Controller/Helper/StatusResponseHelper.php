<?php

namespace Perfumer\Controller\Helper;

use Perfumer\Controller\Exception\ExitActionException;
use Symfony\Component\Validator\ConstraintViolationList;

trait StatusResponseHelper
{
    protected $array = [
        'status' => null,
        'error_message' => null,
        'success_message' => null,
        'errors' => [],
        'content' => null
    ];

    protected function prepareStatusResponseViewVars()
    {
        if ($this->getStatus() === null)
        {
            $status = !($this->getErrorMessage() || $this->hasErrors());
            $this->setStatus($status);
        }

        $this->view->addVars([
            'status' => $this->getStatus(),
            'message' => $this->getStatus() ? $this->getSuccessMessage() : $this->getErrorMessage(),
            'content' => $this->getContent(),
            'errors' => $this->getErrors()
        ]);
    }

    protected function getErrors()
    {
        return $this->array['errors'];
    }

    protected function hasErrors()
    {
        return count($this->array['errors']) > 0;
    }

    protected function addError($key, $value)
    {
        $this->array['errors'][$key] = $value;
    }

    protected function addErrors($errors)
    {
        if ($errors instanceof ConstraintViolationList)
        {
            foreach ($errors as $error)
                $this->array['errors'][$error->getPropertyPath()] = $error->getMessage();
        }
        else
        {
            $this->array['errors'] = array_merge($this->array['errors'], $errors);
        }
    }

    protected function getStatus()
    {
        return $this->array['status'];
    }

    protected function setStatus($status)
    {
        $this->array['status'] = (bool) $status;
    }

    protected function setStatusAndExit($status)
    {
        $this->setStatus($status);

        throw new ExitActionException();
    }

    protected function getSuccessMessage()
    {
        return $this->array['success_message'];
    }

    protected function setSuccessMessage($message)
    {
        $this->setStatus(true);
        
        $this->array['success_message'] = $message;
    }

    protected function setSuccessMessageAndExit($message)
    {
        $this->setSuccessMessage($message);

        throw new ExitActionException();
    }

    protected function getErrorMessage()
    {
        return $this->array['error_message'];
    }

    protected function setErrorMessage($message)
    {
        $this->setStatus(false);

        $this->array['error_message'] = $message;
    }

    protected function setErrorMessageAndExit($message)
    {
        $this->setErrorMessage($message);

        throw new ExitActionException();
    }

    protected function getContent()
    {
        return $this->array['content'];
    }

    protected function setContent($content)
    {
        $this->array['content'] = $content;
    }
}
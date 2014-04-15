<?php

namespace Perfumer\Controller;

use Perfumer\Controller\Exception\ExitActionException;
use Symfony\Component\Validator\ConstraintViolationList;

class JsonController extends CoreController
{
    protected $json = [];

    protected function before()
    {
        parent::before();

        if (!method_exists($this, $this->request->getAction()))
            $this->proxy->forward('exception/json', 'pageNotFound');

        $this->json = [
            'status' => null,
            'error_message' => null,
            'success_message' => null,
            'errors' => [],
            'content' => null
        ];
    }

    protected function after()
    {
        if ($this->getStatus() === null)
        {
            $status = !($this->getErrorMessage() || $this->hasErrors());
            $this->setStatus($status);
        }

        $this->view->setTemplateIfNotDefined('layout/json');

        $this->view->addVars([
            'status' => $this->getStatus(),
            'message' => $this->getStatus() ? $this->getSuccessMessage() : $this->getErrorMessage(),
            'content' => $this->getContent(),
            'errors' => $this->getErrors()
        ]);

        parent::after();
    }

    protected function getErrors()
    {
        return $this->json['errors'];
    }

    protected function hasErrors()
    {
        return count($this->json['errors']) > 0;
    }

    protected function addError($key, $value)
    {
        $this->json['errors'][$key] = $value;
    }

    protected function addErrors($errors)
    {
        if ($errors instanceof ConstraintViolationList)
        {
            foreach ($errors as $error)
                $this->json['errors'][$error->getPropertyPath()] = $error->getMessage();
        }
        else
        {
            $this->json['errors'] = array_merge($this->json['errors'], $errors);
        }
    }

    protected function getStatus()
    {
        return $this->json['status'];
    }

    protected function setStatus($status)
    {
        $this->json['status'] = (bool) $status;
    }

    protected function setStatusAndExit($status)
    {
        $this->setStatus($status);

        throw new ExitActionException();
    }

    protected function getSuccessMessage()
    {
        return $this->json['success_message'];
    }

    protected function setSuccessMessage($message)
    {
        $this->setStatus(true);
        
        $this->json['success_message'] = $message;
    }

    protected function setSuccessMessageAndExit($message)
    {
        $this->setSuccessMessage($message);

        throw new ExitActionException();
    }

    protected function getErrorMessage()
    {
        return $this->json['error_message'];
    }

    protected function setErrorMessage($message)
    {
        $this->setStatus(false);

        $this->json['error_message'] = $message;
    }

    protected function setErrorMessageAndExit($message)
    {
        $this->setErrorMessage($message);

        throw new ExitActionException();
    }

    protected function getContent()
    {
        return $this->json['content'];
    }

    protected function setContent($content)
    {
        $this->json['content'] = $content;
    }
}
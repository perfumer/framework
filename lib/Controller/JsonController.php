<?php

namespace Perfumer\Controller;

use Symfony\Component\Validator\ConstraintViolationList;

class JsonController extends CoreController
{
    protected $status;
    protected $error_message;
    protected $success_message;
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
        $this->status = !($this->error_message || count($this->errors) > 0);

        if (!$this->template)
            $this->template = 'layout/json';

        $this->addViewVars([
            'status' => (int) $this->status,
            'message' => $this->status ? $this->success_message : $this->error_message,
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
}
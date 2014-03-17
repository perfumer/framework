<?php

namespace Perfumer\Controller;

class JsonController extends CoreController
{
    protected $status = false;
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
        if ($this->error_message || count($this->errors) > 0)
            $this->status = false;

        if ($this->success_message)
            $this->status = true;

        $this->addViewVars([
            'status' => (int) $this->status,
            'message' => $this->status ? $this->success_message : $this->error_message,
            'content' => $this->content,
            'errors' => $this->errors
        ]);

        parent::after();
    }

    protected function addErrors(array $errors)
    {
        $this->errors = array_merge($this->errors, $errors);
    }

    protected function addError($name, $value)
    {
        $this->errors[$name] = $value;
    }
}
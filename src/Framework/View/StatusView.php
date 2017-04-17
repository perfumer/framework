<?php

namespace Perfumer\Framework\View;

class StatusView extends SerializeView
{
    /**
     * StatusView constructor.
     * @param mixed $serializer
     */
    public function __construct($serializer = null)
    {
        parent::__construct($serializer);

        $this->addVars([
            'status' => true,
            'message' => null,
            'content' => null
        ]);

        $this->addGroup('errors');
    }

    public function render()
    {
        if (!$this->hasMessage()) {
            $this->deleteVar('message');
        }

        if (!$this->hasContent()) {
            $this->deleteVar('content');
        }

        if (!$this->hasErrors()) {
            $this->deleteVars('errors');
        }

        return parent::render();
    }
    
    /**
     * @return bool
     */
    public function getStatus()
    {
        return $this->getVar('status');
    }

    /**
     * @param bool $status
     */
    public function setStatus($status)
    {
        $this->addVar('status', (bool) $status);
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->getVar('message');
    }

    /**
     * @param string $message
     */
    public function setErrorMessage($message)
    {
        $this->addVars([
            'status' => false,
            'message' => $message
        ]);
    }

    /**
     * @return string $message
     */
    public function setSuccessMessage($message)
    {
        $this->addVars([
            'status' => true,
            'message' => $message
        ]);
    }

    /**
     * @return bool
     */
    public function hasMessage()
    {
        return $this->hasVar('message');
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->getVars('errors');
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getError($key)
    {
        return $this->getVar($key, 'errors');
    }

    /**
     * @param array $errors
     */
    public function addErrors(array $errors)
    {
        $this->addVar('status', false)->addVars($errors, 'errors');
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function addError($key, $value)
    {
        $this->addVar('status', false)->addVar($key, $value, 'errors');
    }

    /**
     * @return bool
     */
    public function hasErrors()
    {
        return $this->hasVars('errors');
    }

    /**
     * @param string $key
     * @return bool
     */
    public function hasError($key)
    {
        return $this->hasVar($key, 'errors');
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->getVar('content');
    }

    /**
     * @param mixed $content
     */
    public function setContent($content)
    {
        $this->addVar('content', $content);
    }

    /**
     * @return bool
     */
    protected function hasContent()
    {
        return $this->hasVar('content');
    }
}

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

        $this->setup();
    }

    public function flush(): void
    {
        parent::flush();

        $this->setup();
    }

    public function setup(): void
    {
        $this->addVars([
            'status' => true,
            'status_code' => null,
            'message' => null,
            'content' => null
        ]);

        $this->addGroup('errors');
    }

    public function render()
    {
        if (!$this->hasStatusCode()) {
            $this->deleteVar('status_code');
        }

        if (!$this->hasMessage()) {
            $this->deleteVar('message');
        }

        if (!$this->hasContent()) {
            $this->deleteVar('content');
        }

        if (!$this->hasErrors()) {
            $this->deleteVars('errors');
            $this->deleteVar('errors');
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
    public function getStatusCode()
    {
        return $this->getVar('status_code');
    }

    /**
     * @param string $status_code
     */
    public function setStatusCode($status_code)
    {
        $this->addVar('status_code', $status_code);
    }

    /**
     * @return bool
     */
    public function hasStatusCode()
    {
        return $this->hasVar('status_code');
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
     * @param string $message
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

    public function getErrors(): array
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

    public function setErrors(array $errors): void
    {
        $this->addErrors($errors);
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

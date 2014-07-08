<?php

namespace Perfumer\Validator\Constraint;

abstract class AbstractConstraint
{
    protected $message = 'constraint.message';
    protected $ready_message;
    protected $placeholders = [];

    abstract public function validate($value);

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    public function getReadyMessage()
    {
        return $this->ready_message;
    }

    public function setReadyMessage($ready_message)
    {
        $this->ready_message = $ready_message;

        return $this;
    }

    public function getPlaceholders()
    {
        return $this->placeholders;
    }

    public function addPlaceholders(array $placeholders = [])
    {
        $this->placeholders = array_merge($this->placeholders, $placeholders);

        return $this;
    }
}
<?php

namespace Perfumer\Validator\Constraint;

abstract class AbstractConstraint
{
    protected $message = 'constraint_message';
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
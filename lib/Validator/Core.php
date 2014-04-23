<?php

namespace Perfumer\Validator;

use Perfumer\Validator\Constraint\AbstractConstraint;

class Core
{
    protected $rules = [];
    protected $messages = [];

    public function __construct()
    {

    }

    public function addRule($field, AbstractConstraint $constraint)
    {
        $this->rules[$field][] = $constraint;

        return $this;
    }

    public function addRules($field, array $constraints)
    {
        $this->rules[$field] = array_merge($this->rules[$field], $constraints);

        return $this;
    }

    public function validate($array)
    {
        foreach ($array as $field => $value)
        {
            if (isset($this->rules[$field]))
            {
                foreach ($this->rules[$field] as $constraint)
                {
                    if (!$constraint->validate($value))
                        $this->messages[$field][] = $constraint->getMessage();
                }
            }
        }

        return count($this->messages) === 0;
    }

    public function getMessages()
    {
        $messages = [];

        foreach ($this->messages as $field => &$message)
        {
            if (count($message) > 1)
                $messages[$field] = $message;
            else
                $messages[$field] = $message[0];
        }

        return $messages;
    }
}
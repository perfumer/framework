<?php

namespace Perfumer\Validator;

use Perfumer\I18n\Core as I18n;
use Perfumer\Validator\Constraint\AbstractConstraint;

class Core
{
    protected $i18n;

    protected $rules = [];
    protected $messages = [];

    public function __construct(I18n $i18n)
    {
        $this->i18n = $i18n;
    }

    public function addRule($field, AbstractConstraint $constraint)
    {
        $this->rules[$field][] = $constraint;

        return $this;
    }

    public function addRules($field, array $constraints)
    {
        if (!isset($this->rules[$field]))
            $this->rules[$field] = [];

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
                    {
                        $this->messages[$field][] = $message = $constraint->getReadyMessage();

                        if (!$message)
                        {
                            $placeholders = $constraint->getPlaceholders();
                            $message = $constraint->getMessage();

                            if (count($placeholders) > 0)
                                $this->messages[$field][] = $this->i18n->translate($message, $placeholders);
                            else
                                $this->messages[$field][] = $this->i18n->translate($message);
                        }
                    }
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
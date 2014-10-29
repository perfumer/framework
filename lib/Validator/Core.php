<?php

namespace Perfumer\Validator;

use Perfumer\Translator\Core as Translator;
use Perfumer\Validator\Constraint\AbstractConstraint;

class Core
{
    protected $translator;

    protected $rules = [];
    protected $messages = [];

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
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
                        if ($message = $constraint->getReadyMessage())
                        {
                            $this->messages[$field][] = $message;
                        }
                        else
                        {
                            $placeholders = $constraint->getPlaceholders();
                            $message = $constraint->getMessage();

                            if (count($placeholders) > 0)
                                $this->messages[$field][] = $this->translator->translate($message, $placeholders);
                            else
                                $this->messages[$field][] = $this->translator->translate($message);
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
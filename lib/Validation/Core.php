<?php

namespace Perfumer\Validation;

use Perfumer\I18n\Core as I18n;
use Respect\Validation\Validator;

class Core
{
    protected $i18n;

    protected $rules = [];
    protected $messages = [];

    public function __construct(I18n $i18n)
    {
        $this->i18n = $i18n;

        class_alias('Perfumer\Validation\Rules\Model', 'Respect\Validation\Rules\Model');
        class_alias('Perfumer\Validation\Exceptions\ModelException', 'Respect\Validation\Exceptions\ModelException');
    }

    public function addRule($field, Validator $validator)
    {
        $this->rules[$field][] = $validator;

        return $this;
    }

    public function validate($array)
    {
        $this->messages = [];

        foreach ($array as $field => $value)
        {
            if (isset($this->rules[$field]))
            {
                foreach ($this->rules[$field] as $validator)
                {
                    try
                    {
                        if ($value === null)
                            $value = '';

                        $validator->assert($value);
                    }
                    catch (\InvalidArgumentException $e)
                    {
                        $messages = [];

                        foreach ($validator->getRules() as $rule)
                        {
                            $name = lcfirst(preg_replace('/.*\\\/', '', get_class($rule)));

                            $messages[$name] = $this->i18n->translate('_validation.' . $name);
                        }

                        foreach ($e->findMessages($messages) as $message)
                        {
                            if ($message)
                                $this->messages[$field][] = $message;
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
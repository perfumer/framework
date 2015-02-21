<?php

namespace Perfumer\Component\Validation;

use Perfumer\Component\Translator\Core as Translator;
use Respect\Validation\Validator;

class Core
{
    protected $translator;

    protected $rules = [];
    protected $messages = [];

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;

        class_alias('Perfumer\Component\Validation\Rules\Model', 'Respect\Validation\Rules\Model');
        class_alias('Perfumer\Component\Validation\Exceptions\ModelException', 'Respect\Validation\Exceptions\ModelException');
        class_alias('Perfumer\Component\Validation\Rules\Unique', 'Respect\Validation\Rules\Unique');
        class_alias('Perfumer\Component\Validation\Exceptions\UniqueException', 'Respect\Validation\Exceptions\UniqueException');
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

                            $messages[$name] = $this->translator->translate('_validation.' . $name);
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
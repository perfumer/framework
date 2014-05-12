<?php

namespace Perfumer\Validator\Constraint;

class RegexConstraint extends AbstractConstraint
{
    protected $message = 'validator.regex';

    protected $regex;

    public function __construct($regex)
    {
        $this->regex = $regex;
    }

    public function validate($value)
    {
        if ($value == '')
            return true;

        return (bool) preg_match($this->regex, (string) $value);
    }
}
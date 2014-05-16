<?php

namespace Perfumer\Validator\Constraint;

class NumericConstraint extends AbstractConstraint
{
    protected $message = 'validator.numeric';

    public function validate($value)
    {
        return $value == '' || is_numeric($value);
    }
}
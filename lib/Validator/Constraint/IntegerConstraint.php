<?php

namespace Perfumer\Validator\Constraint;

class IntegerConstraint extends AbstractConstraint
{
    protected $message = 'validator.integer';

    public function validate($value)
    {
        return $value == '' || (string) (int) $value === (string) $value;
    }
}
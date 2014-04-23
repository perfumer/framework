<?php

namespace Perfumer\Validator\Constraint;

class IntegerConstraint extends AbstractConstraint
{
    public function validate($value)
    {
        return (string) (int) $value === (string) $value;
    }

    public function getMessage()
    {
        return 'validator.integer';
    }
}
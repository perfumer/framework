<?php

namespace Perfumer\Validator\Constraint;

class NotBlankConstraint extends AbstractConstraint
{
    protected $message = 'validator.not_blank';

    public function validate($value)
    {
        return !($value == '');
    }
}
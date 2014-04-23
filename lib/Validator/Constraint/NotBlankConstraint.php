<?php

namespace Perfumer\Validator\Constraint;

class NotBlankConstraint extends AbstractConstraint
{
    public function validate($value)
    {
        return !($value == '');
    }

    public function getMessage()
    {
        return 'validator.not_blank';
    }
}
<?php

namespace Perfumer\Validator\Constraint;

class EmailConstraint extends AbstractConstraint
{
    protected $message = 'validator.email';

    public function validate($value)
    {
        if ($value == '')
            return true;

        if (mb_strlen($value, 'utf-8') > 254)
            return false;

        $expression = '/^[-_a-z0-9\'+*$^&%=~!?{}]++(?:\.[-_a-z0-9\'+*$^&%=~!?{}]+)*+@(?:(?![-.])[-a-z0-9.]+(?<![-.])\.[a-z]{2,6}|\d{1,3}(?:\.\d{1,3}){3})$/iD';

        return (bool) preg_match($expression, (string) $value);
    }
}
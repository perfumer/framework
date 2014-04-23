<?php

namespace Perfumer\Validator\Constraint;

abstract class AbstractConstraint
{
    abstract public function validate($value);

    abstract public function getMessage();
}
<?php

namespace Perfumer\Validator\Constraint;

class ExistenceConstraint extends AbstractConstraint
{
    protected $message = 'validator.existence';

    protected $model_name;

    public function __construct($model_name)
    {
        $this->model_name = (string) $model_name;
    }

    public function validate($value)
    {
        $model = '\\App\\Model\\' . $this->model_name . 'Query';

        $model = $model::create()->findPk($value);

        return (bool) $model;
    }
}
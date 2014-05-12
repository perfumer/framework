<?php

namespace Perfumer\Validator\Constraint;

class UniqueConstraint extends AbstractConstraint
{
    protected $message = 'validator.unique';

    protected $model_name;
    protected $field;
    protected $exceptions = [];

    public function __construct($model_name, $field, $options = [])
    {
        $this->model_name = (string) $model_name;
        $this->field = (string) $field;

        if (isset($options['exceptions']))
            $this->exceptions = $options['exceptions'];
    }

    public function validate($value)
    {
        if (in_array($value, $this->exceptions))
            return true;

        $model = '\\App\\Model\\' . $this->model_name . 'Query';

        $model = $model::create()->filterBy($this->field, $value)->findOne();

        return ! (bool) $model;
    }
}
<?php
namespace Perfumer\Validation\Rules;

use Respect\Validation\Rules\AbstractRule;

class Model extends AbstractRule
{
    protected $model_name;

    public function __construct($model_name)
    {
        $this->model_name = (string) $model_name;
    }

    public function validate($input)
    {
        $model = '\\App\\Model\\' . $this->model_name . 'Query';

        $model = $model::create()->findPk($input);

        return (bool) $model;
    }
}
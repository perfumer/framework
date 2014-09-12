<?php
namespace Perfumer\Validation\Rules;

use Respect\Validation\Rules\AbstractRule;

class Model extends AbstractRule
{
    protected $modelName;

    public function __construct($modelName)
    {
        $this->modelName = (string) $modelName;
    }

    public function validate($input)
    {
        $model = '\\App\\Model\\' . $this->modelName . 'Query';

        $model = $model::create()->findPk($input);

        return (bool) $model;
    }
}
<?php
namespace Perfumer\Validation\Rules;

use Respect\Validation\Rules\AbstractRule;

class Unique extends AbstractRule
{
    protected $modelName;
    protected $field;
    protected $exceptions = [];

    public function __construct($modelName, $field, $options = [])
    {
        $this->modelName = (string) $modelName;
        $this->field = (string) $field;

        if (isset($options['exceptions']) && is_array($options['exceptions']))
            $this->exceptions = $options['exceptions'];
    }

    public function validate($input)
    {
        if (in_array($input, $this->exceptions))
            return true;

        $model = '\\App\\Model\\' . $this->modelName . 'Query';

        $model = $model::create()->filterBy($this->field, $input)->findOne();

        return ! (bool) $model;
    }
}
<?php

namespace Perfumer\Controller\Crud\Basic;

use Perfumer\Controller\Exception\CrudException;

trait CrudSettings
{
    protected function getModelName()
    {
        return null;
    }

    protected function getModel()
    {
        if (!$model_name = $this->getModelName())
            throw new CrudException('Model name for CRUD actions is not defined');

        $model_query = '\\App\\Model\\' . $model_name . 'Query';

        $model = $model_query::create()->findPk($this->getProxy()->getArg('id'));

        if (!$model)
            $this->setErrorMessageAndExit($this->getTranslator()->translate('_crud.object_not_found'));

        return $model;
    }
}
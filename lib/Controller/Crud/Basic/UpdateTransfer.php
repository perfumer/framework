<?php

namespace Perfumer\Controller\Crud\Basic;

use Perfumer\Controller\Exception\CrudException;
use Propel\Runtime\Map\TableMap;

trait UpdateTransfer
{
    public function put()
    {
        $this->putPermission();

        if ($this->proxy->a('id') === null)
            $this->setErrorMessageAndExit('Object not found');

        $fields = $this->container->s('arr')->fetch($this->proxy->a(), $this->putFields());

        if (!$model_name = $this->modelName())
            throw new CrudException('Model name for CRUD update transfer is not defined');

        $model_query = '\\App\\Model\\' . $model_name . 'Query';

        $model = $model_query::create()->findPk($this->proxy->a('id'));

        if (!$model)
            $this->setErrorMessageAndExit('Object not found');

        $valid = $this->putValidate($model, $fields);

        if ($valid)
        {
            $this->putPreSave($model, $fields);

            $model->fromArray($fields, TableMap::TYPE_FIELDNAME);
            $model->save();

            $this->setSuccessMessage('Created');
        }
        else
        {
            $this->setErrorMessage('Errors');
        }
    }

    protected function putPermission()
    {
    }

    protected function putFields()
    {
        return [];
    }

    protected function putValidate($model, array $fields)
    {
        return true;
    }

    protected function putPreSave($model, array $fields)
    {
    }
}
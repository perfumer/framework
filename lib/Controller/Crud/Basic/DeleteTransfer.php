<?php

namespace Perfumer\Controller\Crud\Basic;

use Perfumer\Controller\Exception\CrudException;

trait DeleteTransfer
{
    public function delete()
    {
        $this->deletePermission();

        if ($this->proxy->a('id') === null)
            $this->setErrorMessageAndExit('Object not found');

        if (!$model_name = $this->modelName())
            throw new CrudException('Model name for CRUD delete transfer is not defined');

        $model_query = '\\App\\Model\\' . $model_name . 'Query';

        $model = $model_query::create()->findPk($this->proxy->a('id'));

        if (!$model)
            $this->setErrorMessageAndExit('Object not found');

        $model->delete();

        if ($model->isDeleted())
        {
            $this->deleteAfterSuccess($model);

            $this->setSuccessMessage('Deleted');
        }
    }

    protected function deletePermission()
    {
    }

    protected function deleteAfterSuccess($model)
    {
    }
}
<?php

namespace Perfumer\Controller\Crud\Basic;

use Perfumer\Controller\Exception\CrudException;

trait DeleteTransfer
{
    public function delete()
    {
        $i18n = $this->container->s('i18n');

        $this->deletePermission();

        if ($this->proxy->a('id') === null)
            $this->setErrorMessageAndExit($i18n->translate('crud.object_not_found'));

        if (!$model_name = $this->modelName())
            throw new CrudException('Model name for CRUD delete transfer is not defined');

        $model_query = '\\App\\Model\\' . $model_name . 'Query';

        $model = $model_query::create()->findPk($this->proxy->a('id'));

        if (!$model)
            $this->setErrorMessageAndExit($i18n->translate('crud.object_not_found'));

        $this->deleteValidate($model);

        $model->delete();

        if ($model->isDeleted())
        {
            $this->deleteAfterSuccess($model);

            $this->setSuccessMessage($i18n->translate('crud.deleted'));
        }
    }

    protected function deleteValidate($model)
    {
    }

    protected function deletePermission()
    {
    }

    protected function deleteAfterSuccess($model)
    {
    }
}
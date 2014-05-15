<?php

namespace Perfumer\Controller\Crud\Basic;

use Perfumer\Controller\Exception\CrudException;

trait DeleteTransfer
{
    public function delete()
    {
        $this->deletePermission();

        if ($this->proxy->a('id') === null)
            $this->setErrorMessageAndExit($this->i18n->translate('crud.object_not_found'));

        $model = $this->getModel();

        $this->deleteValidate($model);

        $model->delete();

        if ($model->isDeleted())
        {
            $this->deleteAfterSuccess($model);

            $this->setSuccessMessage($this->i18n->translate('crud.deleted'));
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
<?php

namespace Perfumer\Controller\Crud\Basic;

trait DeleteTransfer
{
    public function delete()
    {
        $this->deletePermission();

        if ($this->getProxy()->getArg('id') === null)
            $this->setErrorMessageAndExit($this->getI18n()->translate('crud.object_not_found'));

        $model = $this->getModel();

        $this->deleteValidate($model);
        $this->deletePreRemove($model);

        if ($this->deleteAction($model))
        {
            $this->deleteAfterSuccess($model);

            $this->setSuccessMessage($this->getI18n()->translate('crud.deleted'));
        }
    }

    protected function deletePermission()
    {
    }

    protected function deleteValidate($model)
    {
    }

    protected function deletePreRemove($model)
    {
    }

    protected function deleteAction($model)
    {
        $model->delete();

        return $model->isDeleted();
    }

    protected function deleteAfterSuccess($model)
    {
    }
}
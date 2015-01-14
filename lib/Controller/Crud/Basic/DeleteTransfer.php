<?php

namespace Perfumer\Controller\Crud\Basic;

use Propel\Runtime\Propel;

trait DeleteTransfer
{
    public function delete()
    {
        $this->deletePermission();

        if ($this->getProxy()->getArg('id') === null)
            $this->setErrorMessageAndExit($this->getTranslator()->translate('crud.object_not_found'));

        $model = $this->getModel();

        $this->deleteValidate($model);
        $this->deletePreRemove($model);

        $con = Propel::getWriteConnection(constant('\\App\\Model\\Map\\' . $this->getModelName() . 'TableMap::DATABASE_NAME'));

        $con->beginTransaction();

        try
        {
            if ($this->deleteAction($model))
            {
                $this->deleteAfterSuccess($model);

                $this->setSuccessMessage($this->getTranslator()->translate('crud.deleted'));
            }

            $con->commit();
        }
        catch (\Exception $e)
        {
            $con->rollback();

            $this->setErrorMessage('При удалении произошла неизвестная ошибка. Попробуйте еще раз.');
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
<?php

namespace Perfumer\Framework\Controller\Crud\Basic;

use Propel\Runtime\Propel;

trait DeleteTransfer
{
    public function delete()
    {
        $this->deletePermission();

        if ($this->getProxy()->getRouter()->getArg('id') === null)
            $this->setErrorMessageAndExit($this->t('_crud.object_not_found'));

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

                $this->setSuccessMessage($this->deleteSuccessMessage($model));
            }

            $con->commit();
        }
        catch (\Exception $e)
        {
            $con->rollback();

            $this->setErrorMessage($this->t('_crud.internal_error'));
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

    protected function deleteSuccessMessage($model)
    {
        return $this->t('_crud.deleted');
    }
}

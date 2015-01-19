<?php

namespace Perfumer\Controller\Crud\Basic;

use Perfumer\Helper\Arr;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Propel;

trait UpdateTransfer
{
    protected $old_model;

    public function put()
    {
        $this->putPermission();

        if ($this->getProxy()->getArg('id') === null)
            $this->setErrorMessageAndExit($this->getTranslator()->translate('_crud.object_not_found'));

        $fields = Arr::fetch($this->getProxy()->getArg(), $this->putFields(), true);

        $model = $this->getModel();

        $this->putValidate($model, $fields);

        if ($this->getView()->getVar('status') === true)
        {
            $this->old_model = clone $model;

            $this->putPrePersist($model, $fields);

            $model->fromArray($fields, TableMap::TYPE_FIELDNAME);

            $this->putPreSave($model, $fields);

            $con = Propel::getWriteConnection(constant('\\App\\Model\\Map\\' . $this->getModelName() . 'TableMap::DATABASE_NAME'));

            $con->beginTransaction();

            try
            {
                if ($model->save() || count($model->getModifiedColumns()) == 0)
                {
                    $this->setContent($model->toArray(TableMap::TYPE_FIELDNAME));
                    $this->setSuccessMessage($this->putSuccessMessage());

                    $this->putAfterSuccess($model, $fields);
                }

                $con->commit();
            }
            catch (\Exception $e)
            {
                $con->rollback();

                $this->setErrorMessage($this->getTranslator()->translate('_crud.internal_error'));
            }
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
    }

    protected function putPrePersist($model, array $fields)
    {
    }

    protected function putPreSave($model, array $fields)
    {
    }

    protected function putAfterSuccess($model, array $fields)
    {
    }

    protected function putSuccessMessage()
    {
        return $this->getTranslator()->translate('_crud.updated');
    }
}
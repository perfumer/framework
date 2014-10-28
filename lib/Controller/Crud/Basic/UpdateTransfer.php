<?php

namespace Perfumer\Controller\Crud\Basic;

use Perfumer\Controller\Exception\CrudException;
use Propel\Runtime\Map\TableMap;

trait UpdateTransfer
{
    protected $old_model;

    public function put()
    {
        $this->putPermission();

        if ($this->getProxy()->getArg('id') === null)
            $this->setErrorMessageAndExit($this->getI18n()->translate('crud.object_not_found'));

        $fields = $this->getContainer()->getService('arr')->fetch($this->getProxy()->getArg(), $this->putFields(), true);

        $model = $this->getModel();

        $this->putValidate($model, $fields);

        if ($this->getView()->getVar('status') === false)
        {
            if (!$this->getView()->getVar('message'))
                $this->setErrorMessage($this->getI18n()->translate('crud.update_errors'));
        }
        else
        {
            $this->old_model = clone $model;

            $this->putPrePersist($model, $fields);

            $model->fromArray($fields, TableMap::TYPE_FIELDNAME);

            $this->putPreSave($model, $fields);

            if ($model->save() || count($model->getModifiedColumns()) == 0)
            {
                $this->setContent($model->toArray(TableMap::TYPE_FIELDNAME));
                $this->setSuccessMessage($this->getI18n()->translate('crud.updated'));

                $this->putAfterSuccess($model, $fields);
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
}
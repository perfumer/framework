<?php

namespace Perfumer\Controller\Crud\Basic;

use Perfumer\Controller\Exception\CrudException;
use Propel\Runtime\Map\TableMap;

trait CreateTransfer
{
    public function post()
    {
        $this->postPermission();

        $fields = $this->getContainer()->getService('arr')->fetch($this->getProxy()->getArg(), $this->postFields(), true);

        if (!$model_name = $this->getModelName())
            throw new CrudException('Model name for CRUD actions is not defined');

        $model_name = '\\App\\Model\\' . $model_name;

        $model = new $model_name();

        $this->postValidate($model, $fields);

        if ($this->getView()->getVar('status') === false)
        {
            if (!$this->getView()->getVar('message'))
                $this->setErrorMessage($this->getI18n()->translate('crud.create_errors'));
        }
        else
        {
            $this->postPrePersist($model, $fields);

            $model->fromArray($fields, TableMap::TYPE_FIELDNAME);

            $this->postPreSave($model, $fields);

            if ($model->save())
            {
                $this->setContent($model->toArray(TableMap::TYPE_FIELDNAME));
                $this->setSuccessMessage($this->getI18n()->translate('crud.created'));

                $this->postAfterSuccess($model, $fields);
            }
        }
    }

    protected function postPermission()
    {
    }

    protected function postFields()
    {
        return [];
    }

    protected function postValidate($model, array $fields)
    {
    }

    protected function postPrePersist($model, array $fields)
    {
    }

    protected function postPreSave($model, array $fields)
    {
    }

    protected function postAfterSuccess($model, array $fields)
    {
    }
}
<?php

namespace Perfumer\Controller\Crud\Basic;

use Perfumer\Controller\Exception\CrudException;
use Propel\Runtime\Map\TableMap;

trait UpdateTransfer
{
    public function put()
    {
        $i18n = $this->container->s('i18n');

        $this->putPermission();

        if ($this->proxy->a('id') === null)
            $this->setErrorMessageAndExit($i18n->translate('crud.object_not_found'));

        $fields = $this->container->s('arr')->fetch($this->proxy->a(), $this->putFields());

        if (!$model_name = $this->modelName())
            throw new CrudException('Model name for CRUD update transfer is not defined');

        $model_query = '\\App\\Model\\' . $model_name . 'Query';

        $model = $model_query::create()->findPk($this->proxy->a('id'));

        if (!$model)
            $this->setErrorMessageAndExit($i18n->translate('crud.object_not_found'));

        $this->putValidate($model, $fields);

        if ($this->hasErrors() || $this->getErrorMessage())
        {
            $this->setErrorMessage($i18n->translate('crud.update_errors'));
        }
        else
        {
            $this->putPreSave($model, $fields);

            $model->fromArray($fields, TableMap::TYPE_FIELDNAME);

            if ($model->save() || count($model->getModifiedColumns()) == 0)
            {
                $this->putAfterSuccess($model, $fields);

                $this->setSuccessMessage($i18n->translate('crud.updated'));
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

    protected function putPreSave($model, array $fields)
    {
    }

    protected function putAfterSuccess($model, array $fields)
    {
    }
}
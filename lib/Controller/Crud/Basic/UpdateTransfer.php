<?php

namespace Perfumer\Controller\Crud\Basic;

use Perfumer\Controller\Exception\CrudException;
use Propel\Runtime\Map\TableMap;

trait UpdateTransfer
{
    public function put()
    {
        $this->putPermission();

        if ($this->proxy->a('id') === null)
            $this->setErrorMessageAndExit($this->i18n->translate('crud.object_not_found'));

        $fields = $this->container->s('arr')->fetch($this->proxy->a(), $this->putFields());

        $model = $this->getModel();

        $this->putValidate($model, $fields);

        if ($this->hasErrors() || $this->getErrorMessage())
        {
            $this->setErrorMessage($this->i18n->translate('crud.update_errors'));
        }
        else
        {
            $this->putPreSave($model, $fields);

            $model->fromArray($fields, TableMap::TYPE_FIELDNAME);

            if ($model->save() || count($model->getModifiedColumns()) == 0)
            {
                $this->setContent($model->toArray(TableMap::TYPE_FIELDNAME));
                $this->setSuccessMessage($this->i18n->translate('crud.updated'));

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

    protected function putPreSave($model, array $fields)
    {
    }

    protected function putAfterSuccess($model, array $fields)
    {
    }
}
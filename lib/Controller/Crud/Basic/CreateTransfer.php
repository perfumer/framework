<?php

namespace Perfumer\Controller\Crud\Basic;

use Perfumer\Controller\Exception\CrudException;
use Propel\Runtime\Map\TableMap;

trait CreateTransfer
{
    public function post()
    {
        $this->postPermission();

        $fields = $this->container->s('arr')->fetch($this->proxy->a(), $this->postFields());

        if (!$model_name = $this->modelName())
            throw new CrudException('Model name for CRUD create transfer is not defined');

        $model_name = '\\App\\Model\\' . $model_name;

        $model = new $model_name();

        $model->fromArray($fields, TableMap::TYPE_FIELDNAME);
        $model->save();

        $this->setSuccessMessage('Created');
    }

    protected function postPermission()
    {
    }

    protected function postFields()
    {
        return [];
    }
}
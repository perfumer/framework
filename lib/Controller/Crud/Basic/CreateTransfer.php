<?php

namespace Perfumer\Controller\Crud\Basic;

use Perfumer\Controller\Exception\CrudException;
use Perfumer\Helper\Arr;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Propel;

trait CreateTransfer
{
    public function post()
    {
        $this->postPermission();

        $fields = Arr::fetch($this->getProxy()->getArg(), $this->postFields(), true);

        if (!$model_name = $this->getModelName())
            throw new CrudException('Model name for CRUD actions is not defined');

        $model_name = '\\App\\Model\\' . $model_name;

        $model = new $model_name();

        $this->postValidate($model, $fields);

        if ($this->getView()->getVar('status') === false)
        {
            if (!$this->getView()->getVar('message'))
                $this->setErrorMessage($this->getTranslator()->translate('crud.create_errors'));
        }
        else
        {
            $this->postPrePersist($model, $fields);

            $model->fromArray($fields, TableMap::TYPE_FIELDNAME);

            $this->postPreSave($model, $fields);

            $con = Propel::getWriteConnection(constant('\\App\\Model\\Map\\' . $this->getModelName() . 'TableMap::DATABASE_NAME'));

            $con->beginTransaction();

            try
            {
                if ($model->save())
                {
                    $this->setContent($model->toArray(TableMap::TYPE_FIELDNAME));
                    $this->setSuccessMessage($this->getTranslator()->translate('crud.created'));

                    $this->postAfterSuccess($model, $fields);
                }

                $con->commit();
            }
            catch (\Exception $e)
            {
                $con->rollback();

                $this->setErrorMessage('При сохранении произошла неизвестная ошибка. Попробуйте еще раз.');
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
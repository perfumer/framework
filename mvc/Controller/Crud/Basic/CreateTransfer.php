<?php

namespace Perfumer\MVC\Controller\Crud\Basic;

use Perfumer\Component\Controller\Exception\CrudException;
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

        if ($this->getView()->getVar('status') === true)
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

                    $this->postAfterSuccess($model, $fields);

                    $this->setSuccessMessage($this->postSuccessMessage($model, $fields));
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

    protected function postSuccessMessage($model, array $fields)
    {
        return $this->getTranslator()->translate('_crud.created');
    }
}
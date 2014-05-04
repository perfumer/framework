<?php

namespace Perfumer\Controller\Helper;

use Symfony\Component\Validator\ConstraintViolationList;

trait ErrorsHelper
{
    protected function errorsBeforeFilter()
    {
        $this->_framework_vars['errors'] = [];
    }

    protected function errorsAfterFilter()
    {
        $this->view->addVar('errors', $this->getErrors());
    }

    protected function getErrors()
    {
        return $this->_framework_vars['errors'];
    }

    protected function hasErrors()
    {
        return count($this->_framework_vars['errors']) > 0;
    }

    protected function addError($key, $value)
    {
        $this->_framework_vars['errors'][$key] = $value;
    }

    protected function addErrors($errors)
    {
        if ($errors instanceof ConstraintViolationList)
        {
            foreach ($errors as $error)
                $this->addError($error->getPropertyPath(), $error->getMessage());
        }
        else
        {
            $this->_framework_vars['errors'] = array_merge($this->_framework_vars['errors'], $errors);
        }
    }
}
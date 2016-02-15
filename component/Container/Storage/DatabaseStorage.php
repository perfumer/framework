<?php

namespace Perfumer\Component\Container\Storage;

use App\Model\ParameterQuery;
use Perfumer\Helper\Arr;
use Propel\Runtime\ActiveQuery\Criteria;

class DatabaseStorage extends AbstractStorage
{
    /**
     * getParamGroup
     * Get array with whole group of parameters. Returns key-value array.
     *
     * @param string $group
     * @return array
     * @access public
     */
    public function getParamGroup($group)
    {
        if (!isset($this->params[$group])) {
            $this->params[$group] = [];

            $parameters = ParameterQuery::create()
                ->filterByGroup($group)
                ->select(['name', 'value'])
                ->find();

            foreach ($parameters as $parameter) {
                $this->params[$group][$parameter['name']] = $parameter['value'];
            }
        }

        return $this->params[$group];
    }

    /**
     * setParam
     * Save one parameter.
     *
     * @param string $group
     * @param string $name
     * @param mixed $value
     * @return boolean
     * @access public
     */
    public function setParam($group, $name, $value)
    {
        if (!isset($this->params[$group])) {
            $this->params[$group] = [];
        }

        $this->params[$group][$name] = $value;

        $parameter = ParameterQuery::create()
            ->filterByGroup($group)
            ->filterByName($name)
            ->findOneOrCreate();

        $parameter->setValue($value);
        $parameter->save();

        return true;
    }

    /**
     * setParamGroup
     *
     * @param string $group
     * @param array $values
     * @return boolean
     * @access public
     */
    public function setParamGroup($group, array $values)
    {
        $parameters = ParameterQuery::create()
            ->filterByGroup($group)
            ->filterByName(array_keys($values), Criteria::NOT_IN)
            ->find();

        if ($parameters) {
            $parameters->delete();
        }

        $this->addParamGroup($group, $values);

        return true;
    }

    /**
     * @param $group
     * @param array $values
     * @return bool
     */
    public function addParamGroup($group, array $values)
    {
        if (!isset($this->params[$group])) {
            $this->params[$group] = [];
        }

        $this->params[$group] = array_merge($this->params[$group], $values);

        foreach ($values as $name => $value) {
            $parameter = ParameterQuery::create()
                ->filterByGroup($group)
                ->filterByName($name)
                ->findOneOrCreate();

            $parameter->setValue($value);
            $parameter->save();
        }

        return true;
    }

    /**
     * @param $group
     * @param array $keys
     * @return bool
     */
    public function deleteParamGroup($group, array $keys = [])
    {
        ParameterQuery::create()
            ->filterByGroup($group)
            ->_if($keys)
                ->filterByName($keys, Criteria::IN)
            ->_endif()
            ->find()
            ->delete();

        if ($keys) {
            if (isset($this->params[$group])) {
                $this->params[$group] = Arr::deleteKeys($this->params[$group], $keys);
            }
        } else {
            if (isset($this->params[$group])) {
                unset($this->params[$group]);
            }
        }

        return true;
    }
}

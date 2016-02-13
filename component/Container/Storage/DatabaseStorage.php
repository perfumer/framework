<?php
namespace Perfumer\Component\Container\Storage;

use App\Model\StorageQuery;
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

            $params = StorageQuery::create()
                ->filterByGroup($group)
                ->select(['name', 'value'])
                ->find();

            foreach ($params as $param) {
                $this->params[$group][$param['name']] = $param['value'];
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

        $storage = StorageQuery::create()
            ->filterByGroup($group)
            ->filterByName($name)
            ->findOneOrCreate();

        $storage->setValue($value);
        $storage->save();

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
        $storage = StorageQuery::create()
            ->filterByGroup($group)
            ->filterByName(array_keys($values), Criteria::NOT_IN)
            ->find();

        if ($storage) {
            $storage->delete();
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
            $storage = StorageQuery::create()
                ->filterByGroup($group)
                ->filterByName($name)
                ->findOneOrCreate();

            $storage->setValue($value);
            $storage->save();
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
        if ($keys) {
            if (isset($this->params[$group])) {
                $this->params[$group] = Arr::deleteKeys($this->params[$group], $keys);
            }

            $storage = StorageQuery::create()
                ->filterByGroup($group)
                ->filterByName($keys, Criteria::IN)
                ->find();

            if ($storage) {
                $storage->delete();
            }
        } else {
            if (isset($this->params[$group])) {
                unset($this->params[$group]);
            }

            $storage = StorageQuery::create()
                ->filterByGroup($group)
                ->find();

            if ($storage) {
                $storage->delete();
            }
        }

        return true;
    }
}

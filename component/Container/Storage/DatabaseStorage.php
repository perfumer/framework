<?php

namespace Perfumer\Component\Container\Storage;

use App\Model\ParameterQuery;

class DatabaseStorage extends AbstractStorage
{
    /**
     * @param string $group
     * @param string $name
     * @param mixed $default
     * @return mixed
     * @access public
     */
    public function getParam($group, $name, $default = null)
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

        return isset($this->params[$group][$name]) ? $this->params[$group][$name] : $default;
    }
}

<?php

namespace Perfumer\Framework\View;

use Perfumer\Framework\View\Exception\ViewException;

abstract class AbstractView
{
    /**
     * @var array
     */
    protected $vars = [];

    /**
     * @var array
     */
    protected $groups = [];

    /**
     * @return string
     */
    abstract public function render();

    /**
     * @param $name
     * @param $group
     * @return mixed
     */
    public function getVar($name, $group = null)
    {
        return $group === null ? $this->vars[$name] : $this->groups[$group][$name];
    }

    /**
     * @param $group
     * @return array
     */
    public function getVars($group = null)
    {
        return $group === null ? $this->vars : $this->groups[$group];
    }

    /**
     * @param $name
     * @param $value
     * @param $group
     * @return $this
     */
    public function addVar($name, $value, $group = null)
    {
        if ($group === null) {
            $this->vars[$name] = $value;
        } else {
            $this->groups[$group][$name] = $value;
        }

        return $this;
    }

    /**
     * @param array $vars
     * @param $group
     * @return $this
     */
    public function addVars(array $vars, $group = null)
    {
        if ($group === null) {
            $this->vars = array_merge($this->vars, $vars);
        } else {
            $this->groups[$group] = array_merge($this->groups[$group], $vars);
        }

        return $this;
    }

    /**
     * @param $name
     * @param $group
     * @return bool
     */
    public function hasVar($name, $group = null)
    {
        return $group === null ? isset($this->vars[$name]) : isset($this->groups[$group][$name]);
    }

    /**
     * @param $group
     * @return bool
     */
    public function hasVars($group = null)
    {
        return $group === null ? count($this->vars) > 0 : count($this->groups[$group]) > 0;
    }

    /**
     * @param $name
     * @param $group
     * @return $this
     */
    public function deleteVar($name, $group = null)
    {
        if ($group === null) {
            unset($this->vars[$name]);
        } else {
            unset($this->groups[$group][$name]);
        }

        return $this;
    }

    /**
     * @param $group
     * @return $this
     */
    public function deleteVars($group = null)
    {
        if ($group === null) {
            $this->vars = [];
        } else {
            unset($this->groups[$group]);
        }

        return $this;
    }

    /**
     * @param $name
     * @param $parent
     * @return $this
     * @throws ViewException
     */
    public function addGroup($name, $parent = null)
    {
        if ($parent === null) {
            $this->vars[$name] = [];

            $base = &$this->vars[$name];
        } else {
            if (!isset($this->groups[$parent])) {
                throw new ViewException('The group "' . $parent . '" is not added yet.');
            }

            $this->groups[$parent][$name] = [];

            $base = &$this->groups[$parent][$name];
        }

        $this->groups[$name] = &$base;

        return $this;
    }
}

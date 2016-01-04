<?php

namespace Perfumer\Framework\View;

use Perfumer\Framework\View\Exception\ViewException;

abstract class AbstractView
{
    protected $vars = [];
    protected $groups = [];

    abstract public function render();

    public function getVar($name, $group = null)
    {
        return $group === null ? $this->vars[$name] : $this->groups[$group][$name];
    }

    public function getVars($group = null)
    {
        return $group === null ? $this->vars : $this->groups[$group];
    }

    public function addVar($name, $value, $group = null)
    {
        if ($group === null)
            $this->vars[$name] = $value;
        else
            $this->groups[$group][$name] = $value;

        return $this;
    }

    public function addVars(array $vars, $group = null)
    {
        if ($group === null)
            $this->vars = array_merge($this->vars, $vars);
        else
            $this->groups[$group] = array_merge($this->groups[$group], $vars);

        return $this;
    }

    public function hasVar($name, $group = null)
    {
        return $group === null ? isset($this->vars[$name]) : isset($this->groups[$group][$name]);
    }

    public function hasVars($group = null)
    {
        return $group === null ? count($this->vars) > 0 : count($this->groups[$group]) > 0;
    }

    public function deleteVar($name, $group = null)
    {
        if ($group === null)
            unset($this->vars[$name]);
        else
            unset($this->groups[$group][$name]);

        return $this;
    }

    public function deleteVars($group = null)
    {
        if ($group === null)
            $this->vars = [];
        else
            unset($this->groups[$group]);

        return $this;
    }

    public function mapGroup($name, $parent = null)
    {
        if ($parent === null)
        {
            $this->vars[$name] = [];

            $base = &$this->vars[$name];
        }
        else
        {
            if (!isset($this->groups[$parent]))
                throw new ViewException('The group "' . $parent . '" is not mapped yet.');

            $this->groups[$parent][$name] = [];

            $base = &$this->groups[$parent][$name];
        }

        $this->groups[$name] = &$base;

        return $this;
    }
}
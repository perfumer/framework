<?php

namespace Perfumer\Framework\View;

use Perfumer\Framework\View\Exception\ViewException;

abstract class AbstractView
{
    protected array $vars = [];
    protected array $groups = [];
    protected array $errors = [];

    /**
     * @return string
     */
    abstract public function render();

    public function flush(): void
    {
        $this->vars = [];
        $this->groups = [];
        $this->errors = [];
    }

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

    public function addVar(string $name, mixed $value, ?string $group = null): static
    {
        if ($group === null) {
            $this->vars[$name] = $value;
        } else {
            $this->groups[$group][$name] = $value;
        }

        return $this;
    }

    public function addVars(array $vars, ?string $group = null): static
    {
        if ($group === null) {
            $this->vars = array_merge($this->vars, $vars);
        } else {
            $this->groups[$group] = array_merge($this->groups[$group], $vars);
        }

        return $this;
    }

    public function hasVar(string $name, ?string $group = null): bool
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
     * @throws ViewException
     */
    public function addGroup(string $name, ?string $parent = null): static
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

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function setErrors(array $errors): void
    {
        $this->errors = $errors;
    }
}

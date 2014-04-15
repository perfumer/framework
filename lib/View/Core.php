<?php

namespace Perfumer\View;

use Perfumer\View\Exception\ViewException;
use Perfumer\View\Templating\AbstractTemplating;

class Core
{
    protected $templating;

    protected $template;
    protected $templating_extension;
    protected $rendering = true;
    protected $vars = [];
    protected $groups = [];

    public function __construct(AbstractTemplating $templating, array $options = [])
    {
        $this->templating = $templating;

        if (isset($options['templating_extension']))
            $this->templating_extension = $options['templating_extension'];
    }

    public function render()
    {
        if (!$this->template)
            throw new ViewException('No template defined.');

        if (!$this->rendering)
            return null;

        $template = $this->template . '.' . $this->templating_extension;

        return $this->templating->render($template, $this->vars);
    }

    public function addVar($name, $value, $group = null)
    {
        if ($group === null)
            $this->vars[$name] = $value;
        else
            $this->groups[$group][$name] = $value;
    }

    public function addVars(array $vars, $group = null)
    {
        if ($group === null)
            $this->vars = array_merge($this->vars, $vars);
        else
            $this->groups[$group] = array_merge($this->groups[$group], $vars);
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

        $this->groups[$name] = $base;
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function setTemplate($template)
    {
        $this->template = $template;
    }

    public function setTemplateIfNotDefined($template)
    {
        if (!$this->template)
            $this->template = $template;
    }

    protected function needsRendering()
    {
        return $this->rendering;
    }

    protected function setRendering($rendering)
    {
        $this->rendering = $rendering;
    }
}
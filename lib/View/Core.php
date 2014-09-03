<?php

namespace Perfumer\View;

use Perfumer\Controller\Exception\ExitActionException;
use Perfumer\View\Exception\ViewException;

class Core
{
    /**
     * @var \Twig_Environment
     */
    protected $twig;

    protected $template;
    protected $vars = [];
    protected $groups = [];

    public function __construct(\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    public function render()
    {
        if (!$this->template)
            throw new ViewException('No template defined.');

        return $this->twig->render($this->template . '.twig', $this->vars);
    }

    public function getVar($name, $group = null)
    {
        $return = null;

        if ($group === null)
            $return = $this->vars[$name];
        else
            $return = $this->groups[$group][$name];

        return $return;
    }

    public function getVars($group = null)
    {
        $return = null;

        if ($group === null)
            $return = $this->vars;
        else
            $return = $this->groups[$group];

        return $return;
    }

    public function addVar($name, $value, $group = null)
    {
        if ($group === null)
            $this->vars[$name] = $value;
        else
            $this->groups[$group][$name] = $value;

        return $this;
    }

    public function addVarAndExit($name, $value, $group = null)
    {
        $this->addVar($name, $value, $group);

        throw new ExitActionException;
    }

    public function addVars(array $vars, $group = null)
    {
        if ($group === null)
            $this->vars = array_merge($this->vars, $vars);
        else
            $this->groups[$group] = array_merge($this->groups[$group], $vars);

        return $this;
    }

    public function addVarsAndExit(array $vars, $group = null)
    {
        $this->addVars($vars, $group);

        throw new ExitActionException;
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

    public function serializeVars($serializer)
    {
        $data = '';

        if ($serializer === 'json')
        {
            $data = json_encode($this->vars);
        }
        elseif (is_callable($serializer))
        {
            $data = $serializer($this->vars);
        }

        return $data;
    }
}
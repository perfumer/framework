<?php

namespace Perfumer\MVC\View;

use Perfumer\MVC\View\Exception\ViewException;
use Perfumer\MVC\View\Router\RouterInterface;

class Core
{
    protected $templating;

    /**
     * @var RouterInterface
     */
    protected $router;

    protected $vars = [];
    protected $groups = [];

    protected $options = [];

    public function __construct($templating, RouterInterface $router, $options = [])
    {
        $this->templating = $templating;
        $this->router = $router;

        $default_options = [
            'extension' => 'php'
        ];

        $this->options = array_merge($default_options, $options);
    }

    public function render($template, $extension = null)
    {
        $template = $this->router->dispatch($template);

        if ($extension === null)
            $extension = $this->options['extension'];

        return $this->templating->render($template . '.' . $extension, $this->vars);
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
        if ($group === null)
            $status = isset($this->vars[$name]);
        else
            $status = isset($this->groups[$group][$name]);

        return $status;
    }

    public function hasVars($group = null)
    {
        if ($group === null)
            $status = count($this->vars) > 0;
        else
            $status = count($this->groups[$group]) > 0;

        return $status;
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
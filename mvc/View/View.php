<?php

namespace Perfumer\MVC\View;

use Perfumer\MVC\View\Exception\ViewException;

class View
{
    /**
     * @var ViewFactory
     */
    protected $factory;

    protected $vars = [];
    protected $groups = [];

    protected $bundle;
    protected $url;

    public function __construct(ViewFactory $factory)
    {
        $this->factory = $factory;
    }

    public function render($bundle = null, $url = null, $vars = [], array $context = [])
    {
        $bundle = $bundle ?: $this->bundle;
        $url = $url ?: $this->url;
        $vars = $vars ? array_merge($this->vars, $vars) : $this->vars;
        $context_bundle = isset($context['bundle']) ? $context['bundle'] : null;

        list($bundle, $url) = $this->factory->getBundler()->overrideTemplate($bundle, $url, $context_bundle);

        $template = $this->factory->getBundler()->getService($bundle, 'view_router')->dispatch($url);

        return $this->factory->getTemplating()->render($template, $vars);
    }

    public function getTemplateBundle()
    {
        return $this->bundle;
    }

    public function getTemplateUrl()
    {
        return $this->url;
    }

    public function setTemplate($bundle, $url)
    {
        $this->bundle = $bundle;
        $this->url = $url;

        return $this;
    }

    public function setTemplateBundle($bundle)
    {
        $this->bundle = $bundle;

        return $this;
    }

    public function setTemplateUrl($url)
    {
        $this->url = $url;

        return $this;
    }

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

    public function serializeVars($serializer = null)
    {
        if ($serializer === 'json')
        {
            $data = json_encode($this->vars);
        }
        elseif (is_callable($serializer))
        {
            $data = $serializer($this->vars);
        }
        else
        {
            $data = serialize($this->vars);
        }

        return $data;
    }
}
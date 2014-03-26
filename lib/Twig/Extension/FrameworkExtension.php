<?php

namespace Perfumer\Twig\Extension;

use Perfumer\Container\Core as Container;

class FrameworkExtension extends \Twig_Extension
{
    protected $container;
    protected $proxy;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->proxy = $container->s('proxy');
    }

    public function getName()
    {
        return 'framework_extension';
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('param', [$this, 'param']),
            new \Twig_SimpleFunction('url', [$this, 'url']),
            new \Twig_SimpleFunction('prefix', [$this, 'prefix']),
            new \Twig_SimpleFunction('id', [$this, 'id']),
            new \Twig_SimpleFunction('query', [$this, 'query']),
            new \Twig_SimpleFunction('arg', [$this, 'arg'])
        ];
    }

    public function param($name)
    {
        return $this->container->p($name);
    }

    public function url($url, $id = null, $query = [], $prefixes = [])
    {
        $generated_url = '/' . trim($url, '/');

        if ($this->container->p('proxy.prefixes'))
        {
            if ($prefixes)
            {
                $prefixes = $this->container->s('arr')->intersect($prefixes, $this->container->p('proxy.prefixes'));
                $prefixes = array_merge($this->proxy->p(), $prefixes);
            }
            else
            {
                $prefixes = $this->proxy->p();
            }

            $generated_url = '/' . implode('/', $prefixes) . $generated_url;
        }

        if ($id)
            $generated_url .= '-' . $id;

        if ($query)
        {
            $query_parts = [];

            foreach ($query as $key => $value)
                $query_parts[] = $key . '=' . urlencode($value);

            $generated_url .= '?' . implode('&', $query_parts);
        }

        return $generated_url;
    }

    public function prefix($name)
    {
        return $this->proxy->getPrefix($name);
    }

    public function id()
    {
        return $this->proxy->getId();
    }

    public function query($name)
    {
        return $this->proxy->getQuery($name);
    }

    public function arg($name)
    {
        return $this->proxy->getArg($name);
    }
}
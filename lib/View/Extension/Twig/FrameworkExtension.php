<?php

namespace Perfumer\View\Extension\Twig;

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
        return $this->proxy->generateUrl($url, $id, $query, $prefixes);
    }

    public function prefix($name = null)
    {
        return $this->proxy->getPrefix($name);
    }

    public function id()
    {
        return $this->proxy->getId();
    }

    public function query($name = null)
    {
        return $this->proxy->getQuery($name);
    }

    public function arg($name = null)
    {
        return $this->proxy->getArg($name);
    }
}
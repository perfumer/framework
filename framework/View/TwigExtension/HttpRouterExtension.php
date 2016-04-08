<?php

namespace Perfumer\Framework\View\TwigExtension;

use Perfumer\Component\Container\Container;

class HttpRouterExtension extends \Twig_Extension
{
    /**
     * @var Container
     */
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function getName()
    {
        return 'http_router_extension';
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('url', [$this, 'url'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('prefix', [$this, 'prefix']),
            new \Twig_SimpleFunction('id', [$this, 'id']),
            new \Twig_SimpleFunction('query', [$this, 'query']),
            new \Twig_SimpleFunction('arg', [$this, 'arg'])
        ];
    }

    public function url($url, $id = null, $query = [], $prefixes = [])
    {
        return $this->getRouter()->generateUrl($url, $id, $query, $prefixes);
    }

    public function prefix($name = null)
    {
        return $this->getRouter()->getPrefix($name);
    }

    public function id($index = null)
    {
        return $this->getRouter()->getId($index);
    }

    public function query($name = null)
    {
        return $this->getRouter()->getQuery($name);
    }

    public function arg($name = null)
    {
        return $this->getRouter()->getArg($name);
    }

    private function getRouter()
    {
        return $this->container->get('proxy')->getRouter();
    }
}

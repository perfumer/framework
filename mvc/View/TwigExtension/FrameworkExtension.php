<?php

namespace Perfumer\MVC\View\TwigExtension;

use Perfumer\Component\Container\Container;

class FrameworkExtension extends \Twig_Extension
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
        return 'framework_extension';
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('request', [$this, 'request'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('tpl', [$this, 'tpl']),
            new \Twig_SimpleFunction('param', [$this, 'param']),
            new \Twig_SimpleFunction('url', [$this, 'url'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('prefix', [$this, 'prefix']),
            new \Twig_SimpleFunction('id', [$this, 'id']),
            new \Twig_SimpleFunction('query', [$this, 'query']),
            new \Twig_SimpleFunction('arg', [$this, 'arg']),
            new \Twig_SimpleFunction('t', [$this, 't'])
        ];
    }

    public function request($bundle, $url, $action, array $args = [], $cache_key = null, $cache_lifetime = 3600)
    {
        $proxy = $this->container->getService('proxy');

        if ($cache_key !== null)
        {
            $cache = $this->container->getService('cache')->getItem($cache_key);

            $content = $cache->get();

            if ($cache->isMiss())
            {
                $cache->lock();

                $content = $proxy->execute($bundle, $url, $action, $args)->getContent();

                $cache->set($content, $cache_lifetime);
            }
        }
        else
        {
            $content = $proxy->execute($bundle, $url, $action, $args)->getContent();
        }

        return $content;
    }

    public function tpl($bundle, $url)
    {
        $bundler = $this->container->getService('bundler');

        list($bundle, $url) = $bundler->overrideTemplate($bundle, $url);

        $template = $bundler->getService($bundle, 'view_router')->dispatch($url);

        return $template;
    }

    public function param($name)
    {
        return $this->container->getParam($name);
    }

    public function url($url, $id = null, $query = [], $prefixes = [])
    {
        return $this->getExternalRouter()->generateUrl($url, $id, $query, $prefixes);
    }

    public function prefix($name = null)
    {
        return $this->getExternalRouter()->getPrefix($name);
    }

    public function id($index = null)
    {
        return $this->getExternalRouter()->getId($index);
    }

    public function query($name = null)
    {
        return $this->getExternalRouter()->getQuery($name);
    }

    public function arg($name = null)
    {
        return $this->getExternalRouter()->getArg($name);
    }

    public function t($key, $placeholders = [])
    {
        return $this->container->getService('translator')->translate($key, $placeholders);
    }

    private function getExternalRouter()
    {
        return $this->container->getService('proxy')->getExternalRouter();
    }
}
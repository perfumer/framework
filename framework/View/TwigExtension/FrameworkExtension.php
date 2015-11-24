<?php

namespace Perfumer\Framework\View\TwigExtension;

use Perfumer\Component\Container\Container;
use Perfumer\Framework\Bundler\Bundler;
use Perfumer\Framework\ExternalRouter\RouterInterface as ExternalRouter;
use Perfumer\Framework\Proxy\Proxy;

class FrameworkExtension extends \Twig_Extension
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var Bundler
     */
    protected $bundler;

    /**
     * @var Proxy
     */
    protected $proxy;

    /**
     * @var ExternalRouter
     */
    protected $external_router;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->bundler = $this->container->getService('bundler');
        $this->proxy = $this->container->getService('proxy');
        $this->external_router = $this->container->getService('external_router');
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
        if ($cache_key !== null)
        {
            $cache = $this->container->getService('cache')->getItem($cache_key);

            $content = $cache->get();

            if ($cache->isMiss())
            {
                $cache->lock();

                $content = $this->proxy->execute($bundle, $url, $action, $args)->getContent();

                $cache->set($content, $cache_lifetime);
            }
        }
        else
        {
            $content = $this->proxy->execute($bundle, $url, $action, $args)->getContent();
        }

        return $content;
    }

    public function tpl($bundle, $url)
    {
        list($bundle, $url) = $this->bundler->overrideTemplate($bundle, $url);

        $template = $this->bundler->getService($bundle, 'view_router')->dispatch($url);

        return $template;
    }

    public function param($name)
    {
        return $this->container->getParam($name);
    }

    public function url($url, $id = null, $query = [], $prefixes = [])
    {
        return $this->external_router->generateUrl($url, $id, $query, $prefixes);
    }

    public function prefix($name = null)
    {
        return $this->external_router->getPrefix($name);
    }

    public function id($index = null)
    {
        return $this->external_router->getId($index);
    }

    public function query($name = null)
    {
        return $this->external_router->getQuery($name);
    }

    public function arg($name = null)
    {
        return $this->external_router->getArg($name);
    }

    public function t($key, $placeholders = [])
    {
        return $this->container->getService('translator')->translate($key, $placeholders);
    }
}
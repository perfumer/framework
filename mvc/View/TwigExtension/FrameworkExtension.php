<?php

namespace Perfumer\MVC\View\TwigExtension;

use Perfumer\Component\Container\Core as Container;

class FrameworkExtension extends \Twig_Extension
{
    /**
     * @var \Perfumer\Component\Container\Core
     */
    protected $container;

    /**
     * @var \Perfumer\MVC\Proxy\Core
     */
    protected $proxy;

    /**
     * @var \Perfumer\Component\Translator\Core
     */
    protected $translator;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->proxy = $container->getService('proxy');
        $this->translator = $container->getService('translator');
    }

    public function getName()
    {
        return 'framework_extension';
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('request', [$this, 'request'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('param', [$this, 'param']),
            new \Twig_SimpleFunction('url', [$this, 'url']),
            new \Twig_SimpleFunction('prefix', [$this, 'prefix']),
            new \Twig_SimpleFunction('id', [$this, 'id']),
            new \Twig_SimpleFunction('query', [$this, 'query']),
            new \Twig_SimpleFunction('arg', [$this, 'arg']),
            new \Twig_SimpleFunction('t', [$this, 't'])
        ];
    }

    public function request($url, $action, array $args = [], $cache_key = null, $cache_lifetime = 3600)
    {
        if ($cache_key !== null)
        {
            $cache = $this->container->getService('cache')->getItem($cache_key);

            $content = $cache->get();

            if ($cache->isMiss())
            {
                $cache->lock();

                $content = $this->proxy->execute($url, $action, $args)->getContent();

                $cache->set($content, $cache_lifetime);
            }
        }
        else
        {
            $content = $this->proxy->execute($url, $action, $args)->getContent();
        }

        return $content;
    }

    public function param($name)
    {
        return $this->container->getParam($name);
    }

    public function url($url, $id = null, $query = [], $prefixes = [])
    {
        return $this->proxy->getExternalRouter()->generateUrl($url, $id, $query, $prefixes);
    }

    public function prefix($name = null)
    {
        return $this->proxy->getExternalRouter()->getPrefix($name);
    }

    public function id($index = null)
    {
        return $this->proxy->getExternalRouter()->getId($index);
    }

    public function query($name = null)
    {
        return $this->proxy->getExternalRouter()->getQuery($name);
    }

    public function arg($name = null)
    {
        return $this->proxy->getExternalRouter()->getArg($name);
    }

    public function t($key, $placeholders = [])
    {
        return $this->translator->translate($key, $placeholders);
    }
}
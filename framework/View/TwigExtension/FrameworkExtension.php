<?php

namespace Perfumer\Framework\View\TwigExtension;

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
            new \Twig_SimpleFunction('t', [$this, 't'])
        ];
    }

    public function request($bundle, $url, $action, array $args = [], $cache_key = null, $cache_lifetime = 3600)
    {
        $proxy = $this->container->get('proxy');

        if ($cache_key !== null)
        {
            $cache = $this->container->get('cache')->getItem($cache_key);

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

    public function tpl($bundle, $template)
    {
        $bundler = $this->container->get('bundler');

        list($bundle, $template) = $bundler->overrideTemplate($bundle, $template);

        $template_provider_service_name = $bundler->getServiceName($bundle, 'template_provider');

        $template = $this->container->get($template_provider_service_name)->dispatch($template);

        return $template;
    }

    public function param($name)
    {
        return $this->container->getParam($name);
    }

    public function t($key, $placeholders = [])
    {
        return $this->container->get('translator')->trans($key, $placeholders);
    }
}

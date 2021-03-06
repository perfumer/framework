<?php

namespace Perfumer\Framework\View\TwigExtension;

use Perfumer\Component\Container\Container;
use Perfumer\Framework\Proxy\Exception\ProxyException;
use Perfumer\Framework\Proxy\Proxy;
use Perfumer\Framework\View\TemplateProvider\ProviderInterface;
use Stash\Pool;

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
        /** @var Proxy $proxy */
        $proxy = $this->container->get('proxy');

        if ($cache_key !== null) {
            /** @var Pool $pool */
            $pool = $this->container->get('cache');

            $item = $pool->getItem($cache_key);

            if ($item->isHit()) {
                $content = $item->get();
            } else {
                $item->lock();

                $content = $proxy->execute($bundle, $url, $action, $args)->getContent();

                $item->set($content);
                $item->expiresAfter($cache_lifetime);
                $item->save();
            }
        } else {
            $content = $proxy->execute($bundle, $url, $action, $args)->getContent();
        }

        return $content;
    }

    public function tpl($module, $template)
    {
        /** @var Proxy $proxy */
        $proxy = $this->container->get('proxy');

        $template_provider_service_name = $proxy->getModuleComponent($module, 'template_provider');

        if (!$template_provider_service_name) {
            throw new ProxyException("Template provider for module '$module' is not defined");
        }

        /** @var ProviderInterface $template_provider */
        $template_provider =  $this->container->get($template_provider_service_name);

        $template = $template_provider->dispatch($template);

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

    public function tc($key, $number, $placeholders = [])
    {
        return $this->container->get('translator')->transChoice($key, $number, $placeholders);
    }
}

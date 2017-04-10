<?php

namespace Perfumer\Framework\View\TwigExtension;

use Perfumer\Component\Container\Container;
use Perfumer\Framework\Router\Http\DefaultRouter;

class HttpRouterExtension extends \Twig_Extension
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'http_router_extension';
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('url', [$this, 'url'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('id', [$this, 'id']),
            new \Twig_SimpleFunction('fields', [$this, 'fields'])
        ];
    }

    /**
     * @param string $url
     * @param mixed $id
     * @param array $query
     * @return string
     */
    public function url($url, $id = null, $query = [])
    {
        return $this->getRouter()->generateUrl($url, $id, $query);
    }

    /**
     * @param int|null $index
     * @return mixed
     */
    public function id($index = null)
    {
        return $this->getRouter()->getId($index);
    }

    /**
     * @param mixed $keys
     * @param mixed $default
     * @return mixed
     */
    public function fields($keys = null, $default = null)
    {
        return $this->getRouter()->getFields($keys, $default);
    }

    /**
     * @return DefaultRouter
     */
    private function getRouter()
    {
        return $this->container->get('proxy')->getRouter();
    }
}

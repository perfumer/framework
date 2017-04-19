<?php

namespace Perfumer\Framework\View\TwigExtension;

use Perfumer\Component\Container\Container;

class ConsoleRouterExtension extends \Twig_Extension
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
        return 'console_router_extension';
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('option', [$this, 'option']),
            new \Twig_SimpleFunction('argument', [$this, 'argument'])
        ];
    }

    public function option($name, $alias = null, $default = null)
    {
        return $this->getRouter()->getOption($name, $alias, $default);
    }

    public function argument($index, $default = null)
    {
        return $this->getRouter()->getArgument($index, $default);
    }

    private function getRouter()
    {
        return $this->container->get('proxy')->getRouter();
    }
}

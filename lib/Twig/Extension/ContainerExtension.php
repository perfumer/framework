<?php

namespace Perfumer\Twig\Extension;

use Perfumer\Container\Core as Container;

class ContainerExtension extends \Twig_Extension
{
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function getName()
    {
        return 'container_extension';
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('param', [$this, 'param'])
        ];
    }

    public function param($name)
    {
        return $this->container->p($name);
    }
}
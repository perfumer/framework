<?php

namespace Perfumer\MVC\View\Router;

class TwigRouter implements RouterInterface
{
    /**
     * @var \Twig_Environment
     */
    protected $twig;

    protected $namespace;

    public function __construct(\Twig_Environment $twig, $root_path, $namespace = null)
    {
        $this->twig = $twig;
        $this->namespace = $namespace;

        $loader = $this->twig->getLoader();

        if ($namespace === null)
        {
            $loader->addPath($root_path);
        }
        elseif (!in_array($namespace, $loader->getNamespaces()))
        {
            $loader->addPath($root_path, $namespace);
        }
    }

    public function dispatch($url)
    {
        $url .= '.twig';

        return $this->namespace ? '@' . $this->namespace . '/' . $url : $url;
    }
}
<?php

namespace Perfumer\Framework\View\TemplateProvider;

class TwigFilesystemProvider implements ProviderInterface
{
    /**
     * @var \Twig_Loader_Filesystem
     */
    protected $loader;

    /**
     * @var string
     */
    protected $namespace;

    /**
     * TwigFilesystemProvider constructor.
     * @param \Twig_Loader_Filesystem $loader
     * @param string $root_path
     * @param string $namespace
     */
    public function __construct(\Twig_Loader_Filesystem $loader, $root_path, $namespace)
    {
        $this->loader = $loader;
        $this->namespace = $namespace;

        if (!in_array($namespace, $loader->getNamespaces())) {
            $loader->addPath($root_path, $namespace);
        }
    }

    /**
     * @param string $template
     * @return string
     */
    public function handle($template)
    {
        $template .= '.twig';

        return $this->namespace ? '@' . $this->namespace . '/' . $template : $template;
    }
}

<?php

namespace Perfumer\Framework\View\TemplateProvider;

use League\Plates\Engine;

class PlatesProvider implements ProviderInterface
{
    /**
     * @var Engine
     */
    protected $plates;

    /**
     * @var string
     */
    protected $namespace;

    /**
     * PlatesProvider constructor.
     * @param Engine $plates
     * @param string $root_path
     * @param string $namespace
     */
    public function __construct(Engine $plates, $root_path, $namespace)
    {
        $this->plates = $plates;
        $this->namespace = $namespace;

        if (!$plates->getFolders()->exists($namespace)) {
            $plates->addFolder($namespace, $root_path);
        }
    }

    /**
     * @param string $template
     * @return string
     */
    public function dispatch($template)
    {
        return $this->namespace . '::' . $template;
    }
}

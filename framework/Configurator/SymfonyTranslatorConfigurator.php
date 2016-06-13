<?php

namespace Perfumer\Framework\Configurator;

use Perfumer\Component\Container\AbstractConfigurator;
use Symfony\Component\Translation\Translator;

class SymfonyTranslatorConfigurator extends AbstractConfigurator
{
    /**
     * @var Translator
     */
    protected $translator;

    /**
     * @param Translator $translator
     */
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'translator';
    }

    /**
     * @param array $resources
     */
    public function configure(array $resources = [])
    {
        if (isset($resources['translator'])) {
            foreach ($resources['translator'] as $resource) {
                $this->translator->addResource('file', [0], [1]);
            }
        }
    }
}
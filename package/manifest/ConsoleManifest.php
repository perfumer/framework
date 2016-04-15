<?php

namespace Perfumer\Package\Manifest;

class ConsoleManifest extends FrameworkManifest
{
    public function getName()
    {
        return 'framework/console';
    }

    public function getDescription()
    {
        return 'Perfumer framework bundle console manifest';
    }

    public function getDefinitionFiles()
    {
        return array_merge(parent::getDefinitionFiles(), [
            __DIR__ . '/../config/services/console.php'
        ]);
    }
}

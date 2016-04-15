<?php

namespace Perfumer\Package\Manifest;

class HttpManifest extends FrameworkManifest
{
    public function getName()
    {
        return 'framework/http';
    }

    public function getDescription()
    {
        return 'Perfumer framework bundle http manifest';
    }

    public function getDefinitionFiles()
    {
        return array_merge(parent::getDefinitionFiles(), [
            __DIR__ . '/../config/services/http.php'
        ]);
    }
}

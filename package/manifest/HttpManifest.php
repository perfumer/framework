<?php

namespace Perfumer\Package\Manifest;

class HttpManifest extends FrameworkManifest
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'framework/http';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return 'Perfumer framework bundle http manifest';
    }

    /**
     * @return array
     */
    public function getDefinitionFiles()
    {
        return array_merge(parent::getDefinitionFiles(), [
            __DIR__ . '/../config/services/http.php'
        ]);
    }
}

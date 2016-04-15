<?php

namespace Perfumer\Package\Manifest;

use Perfumer\Framework\Bundle\AbstractManifest;

class FrameworkManifest extends AbstractManifest
{
    public function getName()
    {
        return 'framework';
    }

    public function getDescription()
    {
        return 'Perfumer framework bundle manifest';
    }

    public function getDefinitionFiles()
    {
        return [
            __DIR__ . '/../config/services/framework.php',
        ];
    }

    public function getParamFiles()
    {
        return [
            __DIR__ . '/../config/params/framework.php',
        ];
    }

    public function getAliases()
    {
        return [
            'request' => 'framework.request'
        ];
    }
}

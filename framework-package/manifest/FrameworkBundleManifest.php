<?php

namespace Perfumer\FrameworkPackage\Manifest;

use Perfumer\Framework\Bundle\AbstractManifest;

class FrameworkBundleManifest extends AbstractManifest
{
    public function getName()
    {
        return 'framework';
    }

    public function getDescription()
    {
        return 'Perfumer framework bundle manifest';
    }

    public function getServices()
    {
        return [
            __DIR__ . '/../config/services/framework.php',
        ];
    }

    public function getAliases()
    {
        return [
            'internal_router' => 'framework.internal_router',
            'view_router' => 'framework.view_router'
        ];
    }
}

<?php

namespace Perfumer\Package\Manifest;

use Perfumer\Framework\Bundle\AbstractManifest;

class FrameworkManifest extends AbstractManifest
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'framework';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return 'Perfumer framework bundle manifest';
    }

    /**
     * @return array
     */
    public function getDefinitionFiles()
    {
        return [
            __DIR__ . '/../config/services/framework.php',
        ];
    }

    /**
     * @return array
     */
    public function getParamFiles()
    {
        return [
            __DIR__ . '/../config/params/framework.php',
        ];
    }

    /**
     * @return array
     */
    public function getAliases()
    {
        return [
            'request' => 'framework.request'
        ];
    }
}

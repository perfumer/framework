<?php

namespace Perfumer\Package\Framework\Bundle;

use Perfumer\Component\Container\AbstractBundle;

abstract class BaseBundle extends AbstractBundle
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
        return 'Perfumer Framework base bundle';
    }

    /**
     * @return array
     */
    public function getDefinitionFiles()
    {
        return [
            __DIR__ . '/../Resource/config/services.php',
        ];
    }

    /**
     * @return array
     */
    public function getResourceFiles()
    {
        return [
            __DIR__ . '/../Resource/config/resources.php',
        ];
    }
}
<?php

namespace Perfumer\Package\Bundle;

use Perfumer\Component\Container\AbstractBundle;

abstract class FrameworkBundle extends AbstractBundle
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

<?php

namespace Perfumer\Package\Bundle;

class ConsoleBundle extends FrameworkBundle
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'framework/console';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return 'Perfumer Framework console bundle';
    }

    /**
     * @return array
     */
    public function getDefinitionFiles()
    {
        return array_merge(parent::getDefinitionFiles(), [
            __DIR__ . '/../config/services/console.php'
        ]);
    }

    /**
     * @return array
     */
    public function getAliases()
    {
        return array_merge(parent::getAliases(), [
            'router' => 'framework.router'
        ]);
    }
}

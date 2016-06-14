<?php

namespace Perfumer\Package\Bundle;

class HttpBundle extends FrameworkBundle
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
        return 'Perfumer Framework http bundle';
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

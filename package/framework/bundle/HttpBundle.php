<?php

namespace Perfumer\Package\Framework\Bundle;

class HttpBundle extends BaseBundle
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

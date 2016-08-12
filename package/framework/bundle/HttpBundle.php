<?php

namespace Perfumer\Package\Framework\Bundle;

class HttpBundle extends BaseBundle
{
    /**
     * @return array
     */
    public function getResources()
    {
        return [
            'bundle_resolver' => [
                'alias' => 'bundle.http_resolver'
            ],
        ];
    }

    /**
     * @return array
     */
    public function getAliases()
    {
        return [
            'request' => 'package.framework.http_request'
        ];
    }
}

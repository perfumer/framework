<?php

namespace Perfumer\Package\Framework\Bundle;

class HttpBundle extends BaseBundle
{
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

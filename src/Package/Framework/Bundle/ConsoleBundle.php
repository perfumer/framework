<?php

namespace Perfumer\Package\Framework\Bundle;

class ConsoleBundle extends BaseBundle
{
    /**
     * @return array
     */
    public function getAliases()
    {
        return [
            'router' => 'router.console',
            'request' => 'package.framework.console_request'
        ];
    }
}

<?php

namespace Perfumer\Framework\Gateway;

interface GatewayInterface
{
    /**
     * @return string
     */
    public function dispatch(): string;
}

<?php

namespace Perfumer\Framework\Controller;

use Perfumer\Framework\Proxy\Response;

interface ControllerInterface
{
    public function _run(): Response;
}

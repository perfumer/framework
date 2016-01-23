<?php

namespace Perfumer\Framework\ExternalRouter;

use Perfumer\Framework\Proxy\Response;

interface RouterInterface
{
    public function getName();

    public function isHttp();

    public function dispatch();

    public function getExternalResponse();

    public function sendResponse(Response $response);
}

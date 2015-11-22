<?php

namespace Perfumer\MVC\ExternalRouter;

use Perfumer\MVC\Proxy\Response;

interface RouterInterface
{
    public function dispatch();

    public function getExternalResponse();

    public function sendResponse(Response $response);
}
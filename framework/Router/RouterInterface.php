<?php

namespace Perfumer\Framework\Router;

use Perfumer\Framework\Proxy\Response;

interface RouterInterface
{
    public function getAllowedActions();

    public function getNotFoundAttributes();

    public function isHttp();

    public function dispatch();

    public function getExternalResponse();

    public function sendResponse(Response $response);
}

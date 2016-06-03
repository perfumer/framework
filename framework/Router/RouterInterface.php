<?php

namespace Perfumer\Framework\Router;

use Perfumer\Framework\Proxy\Response as InternalResponse;

interface RouterInterface
{
    public function getAllowedActions();

    public function getNotFoundAttributes();

    public function isHttp();

    public function dispatch();

    public function getResponse();

    public function sendResponse(InternalResponse $response);
}

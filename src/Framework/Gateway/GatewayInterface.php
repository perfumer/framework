<?php

namespace Perfumer\Framework\Gateway;

use Perfumer\Framework\Proxy\Response;

interface GatewayInterface
{
    /**
     * @param $request mixed
     * @return string
     */
    public function dispatch($request): string;

    /**
     * @return mixed
     */
    public function createRequestFromGlobals();

    /**
     * @return mixed
     */
    public function createResponse();

    /**
     * @param $response
     * @param Response $internal_response
     */
    public function sendResponse($response, Response $internal_response): void;
}

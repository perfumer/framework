<?php

namespace Perfumer\Framework\Router;

use Perfumer\Framework\Proxy\Response as InternalResponse;

interface RouterInterface
{
    /**
     * @return array
     */
    public function getAllowedActions();

    /**
     * @return array
     */
    public function getNotFoundAttributes();

    /**
     * @return bool
     */
    public function isHttp();

    /**
     * @return array
     */
    public function dispatch();

    /**
     * @return mixed
     */
    public function getResponse();

    /**
     * @param InternalResponse $response
     */
    public function sendResponse(InternalResponse $response);
}

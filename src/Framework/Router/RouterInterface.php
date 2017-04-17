<?php

namespace Perfumer\Framework\Router;

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
     * @param string $content
     */
    public function sendResponse($content);
}

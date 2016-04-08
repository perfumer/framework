<?php

namespace Perfumer\Framework\Router\Http;

trait FastRouteRouterControllerHelpers
{
    /**
     * Shortcut for FastRouteRouter getFields() method
     *
     * @param string|array|null $keys
     * @param mixed $default
     * @return mixed
     */
    protected function f($keys = null, $default = null)
    {
        return $this->getRouter()->getFields($keys, $default);
    }
}

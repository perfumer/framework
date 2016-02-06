<?php

namespace Perfumer\Component\Session\TokenHandler;

abstract class AbstractHandler
{
    /**
     * @return string|null
     */
    abstract public function getToken();

    /**
     * @param string $token
     */
    abstract public function setToken($token);

    abstract public function deleteToken();

    /**
     * @return int
     */
    abstract public function getTokenLifetime();
}

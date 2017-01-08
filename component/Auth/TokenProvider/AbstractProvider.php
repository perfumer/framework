<?php

namespace Perfumer\Component\Auth\TokenProvider;

abstract class AbstractProvider
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

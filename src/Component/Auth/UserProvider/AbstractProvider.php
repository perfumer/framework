<?php

namespace Perfumer\Component\Auth\UserProvider;

abstract class AbstractProvider
{
    /**
     * @param string $token
     * @return mixed
     */
    abstract public function getUserId($token);

    /**
     * @param string $token
     * @param mixed $id
     * @return bool
     */
    abstract public function setUserToken($token, $id);
}
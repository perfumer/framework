<?php

namespace Perfumer\Component\Session\TokenHandler;

abstract class AbstractHandler
{
    abstract public function getToken();

    abstract public function setToken($token);

    abstract public function deleteToken();

    abstract public function getTokenLifetime();
}

<?php

namespace Perfumer\Component\Auth\TokenHandler;

abstract class AbstractHandler
{
    abstract public function getToken();

    abstract public function setToken($token);

    abstract public function deleteToken();

    abstract public function getTokenLifetime();
}
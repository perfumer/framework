<?php

namespace Perfumer\Auth\TokenHandler;

abstract class AbstractHandler
{
    abstract public function getToken();

    abstract public function setToken($token);

    abstract public function deleteToken();
}
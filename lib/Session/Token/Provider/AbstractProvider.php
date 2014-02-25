<?php

namespace Perfumer\Session\Token\Provider;

abstract class AbstractProvider
{
    abstract public function getToken();
}
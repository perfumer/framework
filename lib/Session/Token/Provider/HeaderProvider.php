<?php

namespace Perfumer\Session\Token\Provider;

class HeaderProvider extends AbstractProvider
{
    protected $header_name;

    public function __construct($header_name)
    {
        $this->header_name = $header_name;
    }

    public function getToken()
    {
        $header = 'HTTP_' . $this->header_name;

        return isset($_SERVER[$header]) ? $_SERVER[$header] : null;
    }
}
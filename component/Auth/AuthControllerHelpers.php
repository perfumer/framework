<?php

namespace Perfumer\Component\Auth;

trait AuthControllerHelpers
{
    protected $_auth;

    /**
     * Default name of Auth service
     *
     * @var string
     */
    protected $_auth_service_name = 'auth';

    /**
     * @return Authentication
     */
    protected function getAuth()
    {
        if ($this->_auth === null)
            $this->_auth = $this->getContainer()->getService($this->_auth_service_name);

        return $this->_auth;
    }

    protected function getUser()
    {
        return $this->getAuth()->getUser();
    }
}
<?php

namespace Perfumer\Component\Auth\UserProvider;

class PropelProvider extends AbstractProvider
{
    /**
     * @var array
     */
    protected $options = [];

    /**
     * PropelProvider constructor.
     * @param array $options
     */
    public function __construct($options = [])
    {
        $default_options = [
            'lifetime' => 3600
        ];

        $this->options = array_merge($default_options, $options);
    }

    public function getUserId($token)
    {
        // TODO: Implement getUserId() method.
    }

    public function setUserToken($token, $id)
    {
        // TODO: Implement setUserToken() method.
    }

    public function deleteUserToken($token)
    {
        // TODO: Implement deleteUserToken() method.
    }
}

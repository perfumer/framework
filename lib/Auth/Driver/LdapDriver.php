<?php

namespace Perfumer\Auth\Driver;

use App\Model\User;
use App\Model\UserQuery;
use Perfumer\Auth\Exception\AuthException;
use Symfony\Component\HttpFoundation\Session\Session;

class LdapDriver extends DatabaseDriver
{
    protected $ldap_hostname;
    protected $ldap_domain;

    public function __construct(Session $session, $options = [])
    {
        parent::__construct($session, $options);

        if (isset($options['ldap_hostname']))
            $this->ldap_hostname = $options['ldap_hostname'];

        if (isset($options['ldap_domain']))
            $this->ldap_domain = $options['ldap_domain'];
    }

    public function login($username, $password, $force_login = false)
    {
        try
        {
            $user = UserQuery::create()->findOneByUsername($username);

            if (!$user)
                throw new AuthException(self::STATUS_INVALID_USERNAME);

            $connection = ldap_connect($this->ldap_hostname);

            if (!$connection)
                throw new AuthException(self::STATUS_REMOTE_SERVER_ERROR);

            if (!@ldap_bind($connection, $this->ldap_domain . '\\' . $username, $password))
                throw new AuthException(self::STATUS_INVALID_CREDENTIALS);
        }
        catch(AuthException $e)
        {
            $this->user = new User();
            $this->status = $e->getMessage();
            return;
        }

        $this->user = $user;
        $this->user->setLogged(true);
        $this->user->loadPermissions();
        $this->status = self::STATUS_SIGNED_IN;

        $this->updateToken();
    }
}
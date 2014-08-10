<?php

namespace Perfumer\Auth\Driver;

use App\Model\User;
use App\Model\UserQuery;
use Perfumer\Auth\Exception\AuthException;
use Symfony\Component\HttpFoundation\Session\Session;

class LdapDriver extends DatabaseDriver
{
    protected $ldap_hostname;
    protected $ldap_port = 636;
    protected $ldap_domain;
    protected $pull_user = false;

    public function __construct(Session $session, $options = [])
    {
        parent::__construct($session, $options);

        if (isset($options['ldap_hostname']))
            $this->ldap_hostname = $options['ldap_hostname'];

        if (isset($options['ldap_port']))
            $this->ldap_port = $options['ldap_port'];

        if (isset($options['ldap_domain']))
            $this->ldap_domain = $options['ldap_domain'];

        if (isset($options['pull_user']))
            $this->pull_user = $options['pull_user'];
    }

    public function login($username, $password, $force_login = false)
    {
        $user = new User();

        try
        {
            if (!$this->pull_user)
            {
                $user = UserQuery::create()->findOneByUsername($username);

                if (!$user)
                    throw new AuthException(self::STATUS_INVALID_USERNAME);
            }

            if (!$force_login)
            {
                $connection = ldap_connect($this->ldap_hostname, $this->ldap_port);

                if (!$connection)
                    throw new AuthException(self::STATUS_REMOTE_SERVER_ERROR);

                if (!@ldap_bind($connection, $username . '@' . $this->ldap_domain, $password))
                    throw new AuthException(self::STATUS_INVALID_CREDENTIALS);
            }
        }
        catch(AuthException $e)
        {
            $this->user = $user;
            $this->status = $e->getMessage();
            return;
        }

        if ($this->pull_user)
        {
            $user->setUsername($username);
            $user->hashPassword($password);
        }

        $this->user = $user;
        $this->user->setLogged(true);
        $this->user->loadPermissions();
        $this->status = self::STATUS_SIGNED_IN;

        $this->updateToken();
    }
}
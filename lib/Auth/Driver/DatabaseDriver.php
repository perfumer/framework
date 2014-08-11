<?php

namespace Perfumer\Auth\Driver;

use App\Model\Token;
use App\Model\TokenQuery;
use App\Model\User;
use App\Model\UserQuery;
use Perfumer\Auth\Exception\AuthException;
use Symfony\Component\HttpFoundation\Session\Session;

class DatabaseDriver
{
    const STATUS_ACCOUNT_BANNED = 1;
    const STATUS_ACCOUNT_DISABLED = 2;
    const STATUS_AUTHENTICATED = 3;
    const STATUS_INVALID_CREDENTIALS = 4;
    const STATUS_INVALID_PASSWORD = 5;
    const STATUS_INVALID_USERNAME = 6;
    const STATUS_NO_TOKEN = 7;
    const STATUS_NON_EXISTING_TOKEN = 8;
    const STATUS_NON_EXISTING_USER = 9;
    const STATUS_REMOTE_SERVER_ERROR = 10;
    const STATUS_SIGNED_IN = 11;
    const STATUS_SIGNED_OUT = 12;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\Session;
     */
    protected $session;

    /**
     * @var \App\Model\User
     */
    protected $user;

    protected $status;
    protected $update_gap = 3600;

    public function __construct(Session $session, $options = [])
    {
        $this->session = $session;
        $this->user = new User();

        if (isset($options['update_gap']))
            $this->update_gap = $options['update_gap'];
    }

    public function isLogged()
    {
        if ($this->status === null)
            $this->init();

        return $this->user->isLogged();
    }

    public function getUser()
    {
        if ($this->status === null)
            $this->init();

        return $this->user;
    }

    public function getStatus()
    {
        if ($this->status === null)
            $this->init();

        return $this->status;
    }

    public function init()
    {
        $update_token = false;

        try
        {
            if ($this->session->get('auth.user') === null)
                throw new AuthException(self::STATUS_NO_TOKEN);

            $user = null;

            if ($data = $this->session->get('auth.user'))
            {
                $user = new User();
                $user->fromArray($data);
                $user->setNew(false);
            }

            if ($user)
            {
                if(time() - $this->session->get('auth.updated') >= $this->update_gap)
                {
                    $user = UserQuery::create()->findPk($user->getId());

                    if (!$user)
                        throw new AuthException(self::STATUS_NON_EXISTING_USER);

                    $update_token = true;
                }
            }
            else
            {
                $token = TokenQuery::create()->findOneByToken($this->session->getId());

                if (!$token)
                    throw new AuthException(self::STATUS_NON_EXISTING_TOKEN);

                $user = $token->getUser();

                $update_token = true;
            }

            $this->user = $user;
            $this->user->setLogged(true);
            $this->user->loadPermissions();
            $this->status = self::STATUS_AUTHENTICATED;

            if ($update_token)
            {
                $this->updateToken();
            }
        }
        catch (AuthException $e)
        {
            $this->user = new User();
            $this->status = $e->getMessage();
        }
    }

    public function login($username, $password, $force_login = false)
    {
        try
        {
            $user = UserQuery::create()->findOneByUsername($username);

            if (!$user)
                throw new AuthException(self::STATUS_INVALID_USERNAME);

            if (!$force_login && !$user->validatePassword($password))
                throw new AuthException(self::STATUS_INVALID_PASSWORD);
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

    public function logout()
    {
        $this->session->invalidate();
        $this->user = new User();
        $this->status = self::STATUS_SIGNED_OUT;
    }

    protected function updateToken()
    {
        $this->session->set('auth.updated', time());
        $this->session->set('auth.user', $this->user->toArray());
        $this->session->migrate();

        $token = new Token();
        $token->setToken($this->session->getId());
        $token->setUser($this->user);
        $token->save();
    }
}
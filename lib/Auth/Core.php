<?php

namespace Perfumer\Auth;

use App\Model\Token;
use App\Model\TokenQuery;
use App\Model\User;
use App\Model\UserQuery;
use Perfumer\Auth\Exception\AuthException;
use Perfumer\Session\AbstractSession as Session;
use Perfumer\Session\Token\Provider\AbstractProvider as TokenProvider;

class Core
{
    const STATUS_ACCOUNT_BANNED = 1;
    const STATUS_ACCOUNT_DISABLED = 2;
    const STATUS_AUTHENTICATED = 3;
    const STATUS_INVALID_PASSWORD = 4;
    const STATUS_INVALID_USERNAME = 5;
    const STATUS_NO_TOKEN = 6;
    const STATUS_NON_EXISTING_TOKEN = 7;
    const STATUS_NON_EXISTING_USER = 8;
    const STATUS_SIGNED_IN = 9;
    const STATUS_SIGNED_OUT = 10;

    protected $session;
    protected $token_provider;
    protected $user;

    protected $status;
    protected $update_gap;

    public function __construct(Session $session, TokenProvider $token_provider, $options = [])
    {
        $this->session = $session;
        $this->token_provider = $token_provider;
        $this->user = new User();

        $this->update_gap = !empty($options['update_gap']) ? (int) $options['update_gap'] : 3600;

        if (!$session->isStarted())
        {
            $token = $this->token_provider->getToken();
            $session->start($token);
        }
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
            if ($this->token_provider->getToken() === null)
                throw new AuthException(self::STATUS_NO_TOKEN);

            $user = null;

            if ($data = $this->session->get('auth.user'))
            {
                $user = new User();
                $user->fromArray(unserialize($data));
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
        $this->session->restart();
        $this->user = new User();
        $this->status = self::STATUS_SIGNED_OUT;
    }

    protected function updateToken()
    {
        $this->session->set('auth.updated', time());
        $this->session->set('auth.user', serialize($this->user->toArray()));

        $token = new Token();
        $token->setToken($this->session->regenerate());
        $token->setUser($this->user);
        $token->save();
    }
}
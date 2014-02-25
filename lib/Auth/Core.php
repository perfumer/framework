<?php

namespace Perfumer\Auth;

use App\Model\Token;
use App\Model\TokenQuery;
use App\Model\UserQuery;
use Perfumer\Auth\Exception as AuthException;
use Perfumer\Session\AbstractSession as Session;

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

    protected $is_logged = false;
    protected $status;
    protected $user;

    protected $update_gap = 3600;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function isLogged()
    {
        return $this->is_logged;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function init()
    {
        $update_token = false;

        try
        {
            if (!$this->session->isStarted())
                throw new AuthException(self::STATUS_NO_TOKEN);

            $user = null;

            if ($data = $this->session->get('auth.user'))
            {
                $user = new User();
                $user->fromArray(unserialize($data));
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
            $this->is_logged = true;
            $this->status = self::STATUS_AUTHENTICATED;

            if ($update_token)
            {
                $this->updateToken();

                $this->user->loadPermissions();
            }
        }
        catch (AuthException $e)
        {
            $this->is_logged = false;
            $this->user = null;
            $this->status = $e->getMessage();
        }
    }

    public function signIn($username, $password, $force_login = false)
    {
        try
        {
            $user = UserQuery::create()->findOneByUsername($username);

            if (!$user)
                throw new AuthException(self::STATUS_INVALID_USERNAME);

            if (!$force_login && !$this->user->checkPassword($password))
                throw new AuthException(self::STATUS_INVALID_PASSWORD);
        }
        catch(AuthException $e)
        {
            $this->is_logged = false;
            $this->user = null;
            $this->status = $e->getMessage();
            return;
        }

        $this->is_logged = true;
        $this->user = $user;
        $this->status = self::STATUS_SIGNED_IN;

        $this->updateToken();
        $this->user->loadPermissions();
    }

    public function signOut()
    {
        $this->session->restart();
        $this->is_logged = false;
        $this->user = null;
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
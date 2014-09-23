<?php

namespace Perfumer\Auth\Driver;

use App\Model\Token;
use App\Model\TokenQuery;
use App\Model\User;
use App\Model\UserQuery;
use Perfumer\Auth\Exception\AuthException;
use Perfumer\Auth\TokenHandler\AbstractHandler as TokenHandler;
use Perfumer\Session\Core as SessionService;
use Perfumer\Session\Item as Session;

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
     * @var SessionService
     */
    protected $session_service;

    /**
     * @var TokenHandler
     */
    protected $token_handler;

    /**
     * @var \App\Model\User
     */
    protected $user;

    /**
     * @var Session
     */
    protected $session;

    protected $token;
    protected $status;
    protected $update_gap = 3600;

    public function __construct(SessionService $session_service, TokenHandler $token_handler, $options = [])
    {
        $this->session_service = $session_service;
        $this->token_handler = $token_handler;
        $this->user = new User();

        $this->token = $this->token_handler->getToken();

        if ($this->token !== null)
            $this->session = $this->session_service->get($this->token);

        if (isset($options['update_gap']))
            $this->update_gap = (int) $options['update_gap'];
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

    public function getSession()
    {
        return $this->session;
    }

    public function init()
    {
        $start_session = false;
        $update_session = false;

        try
        {
            if ($this->token === null)
                throw new AuthException(self::STATUS_NO_TOKEN);

            $user = null;

            if ($data = $this->session->get('_user'))
            {
                $user = new User();
                $user->fromArray($data);
                $user->setPermissions($this->session->get('_user_permissions'));
                $user->setRoleIds($this->session->get('_user_role_ids'));
                $user->setNew(false);
            }

            if ($user)
            {
                if(time() - $this->session->get('_last_updated') >= $this->update_gap)
                {
                    $user = UserQuery::create()->findPk($user->getId());

                    if (!$user)
                        throw new AuthException(self::STATUS_NON_EXISTING_USER);

                    $update_session = true;
                }
            }
            else
            {
                $token = TokenQuery::create()->findOneByToken($this->token);

                if (!$token)
                    throw new AuthException(self::STATUS_NON_EXISTING_TOKEN);

                $user = $token->getUser();

                $start_session = true;
            }

            $this->user = $user;
            $this->user->setLogged(true);

            if ($start_session)
                $this->startSession();

            if ($update_session)
                $this->updateSession();

            $this->status = self::STATUS_AUTHENTICATED;

            $this->token_handler->setToken($this->token);
        }
        catch (AuthException $e)
        {
            $this->reset($e->getMessage());
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

            $this->user = $user;
            $this->user->setLogged(true);

            $this->startSession();

            $this->status = self::STATUS_SIGNED_IN;

            $this->token_handler->setToken($this->token);
        }
        catch (AuthException $e)
        {
            $this->reset($e->getMessage());
        }
    }

    public function logout()
    {
        $this->reset(self::STATUS_SIGNED_OUT);
    }

    protected function startSession()
    {
        if ($this->session !== null)
            $this->session->destroy();

        $this->session = $this->session_service->get();

        $this->updateSessionData();

        $this->token = $this->session->getId();

        $token = new Token();
        $token->setToken($this->token);
        $token->setUser($this->user);
        $token->save();

        // Clear old tokens in the database
        $old_date = (new \DateTime())->modify('-' . $this->token_handler->getTokenLifetime() . ' second');

        TokenQuery::create()->filterByUser($this->user)->filterByUpdatedAt($old_date, '<')->delete();
    }

    protected function updateSession()
    {
        $token = TokenQuery::create()->findOneByToken($this->token);

        if ($token)
            $token->setUpdatedAt(new \DateTime())->save();

        $this->updateSessionData();
    }

    protected function updateSessionData()
    {
        $this->user->revealRoles();

        $this->session
            ->set('_last_updated', time())
            ->set('_user', $this->user->toArray())
            ->set('_user_permissions', $this->user->getPermissions())
            ->set('_user_role_ids', $this->user->getRoleIds());
    }

    public function invalidateSessions($user = null)
    {
        if ($user === null)
            $user = $this->user;

        $old_date = (new \DateTime())->modify('-' . $this->token_handler->getTokenLifetime() . ' second');

        $tokens = TokenQuery::create()
            ->filterByUser($user)
            ->filterByUpdatedAt($old_date, '>=')
            ->find();

        foreach ($tokens as $token)
        {
            $session = $this->session_service->get($token->getToken());

            if ($session->has('_last_updated'))
                $session->set('_last_updated', 0);
        }
    }

    protected function reset($status)
    {
        $this->user = new User();

        $this->status = $status;

        if ($this->session !== null)
            $this->session->destroy();

        if ($this->token !== null)
            $this->token_handler->deleteToken();
    }
}
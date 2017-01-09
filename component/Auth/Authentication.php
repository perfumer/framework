<?php

namespace Perfumer\Component\Auth;

use Perfumer\Component\Auth\Exception\AuthException;
use Perfumer\Component\Auth\UserProvider\AbstractProvider as UserProvider;
use Perfumer\Component\Auth\TokenProvider\AbstractProvider as TokenProvider;
use Perfumer\Helper\Text;

class Authentication
{
    /**
     * @var UserProvider
     */
    protected $user_provider;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var TokenProvider
     */
    protected $token_provider;

    /**
     * @var bool
     */
    protected $is_user = false;

    /**
     * @var bool
     */
    protected $is_anonymous = false;

    /**
     * @var bool
     */
    protected $is_processed = false;

    /**
     * @var mixed
     */
    protected $id;

    /**
     * @var string
     */
    protected $token;

    /**
     * @param UserProvider $user_provider
     * @param Session $session
     * @param TokenProvider $token_provider
     */
    public function __construct(UserProvider $user_provider, Session $session, TokenProvider $token_provider)
    {
        $this->user_provider = $user_provider;
        $this->session = $session;
        $this->token_provider = $token_provider;
    }

    /**
     * @return bool
     */
    public function isAuthenticated()
    {
        $this->authenticate();

        return $this->is_user || $this->is_anonymous;
    }

    /**
     * @return bool
     */
    public function isUser()
    {
        $this->authenticate();

        return $this->is_user;
    }

    /**
     * @return bool
     */
    public function isAnonymous()
    {
        $this->authenticate();

        return $this->is_anonymous;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        $this->authenticate();

        return $this->id;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        $this->authenticate();

        return $this->token;
    }

    protected function authenticate()
    {
        if ($this->is_processed === true) {
            return;
        }

        $this->token = $this->token_provider->getToken();

        try {
            if ($this->token === null) {
                throw new AuthException();
            }

            if (!$this->session->has($this->token)) {
                $this->id = $this->user_provider->getUserId($this->token);

                if (!$this->id) {
                    throw new AuthException();
                }

                $this->is_user = true;

                $this->session->set($this->token, $this->id);
            } else {
                $this->id = $this->session->get($this->token);

                if (is_int($this->id)) {
                    $this->is_user = true;
                } else {
                    $this->is_anonymous = true;

                    $this->session->set($this->token, $this->id);
                }
            }

            $this->is_processed = true;

            $this->token_provider->setToken($this->token);
        } catch (AuthException $e) {
            $this->logout();
        }
    }

    /**
     * @return bool
     */
    public function startAnonymousSession()
    {
        if ($this->isAuthenticated()) {
            return false;
        }

        $this->token = $this->session->generateId();
        $this->id = Text::generateAlphabeticString(20);
        $this->is_anonymous = true;

        $this->session->set($this->token, $this->id);

        $this->token_provider->setToken($this->token);

        return true;
    }

    /**
     * @param int $id
     * @return bool
     */
    public function startUserSession($id)
    {
        if ($this->isAuthenticated()) {
            return false;
        }

        $this->token = $this->session->generateId();
        $this->id = $id;

        $set = $this->user_provider->setUserToken($this->token, $this->id);

        if ($set) {
            $this->is_user = true;

            $this->session->set($this->token, $this->id);

            $this->token_provider->setToken($this->token);
        }

        return $set;
    }

    public function logout()
    {
        $this->id = null;
        $this->is_user = false;
        $this->is_anonymous = false;
        $this->is_processed = true;

        if ($this->token !== null) {
            $this->session->destroy($this->token);
            $this->token_provider->deleteToken();
        }
    }

    /**
     * @param array $tokens
     */
    public function destroySessions($tokens)
    {
        if (is_array($tokens)) {
            foreach ($tokens as $token) {
                $this->session->destroy($token);
            }
        }
    }
}

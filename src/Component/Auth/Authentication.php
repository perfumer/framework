<?php

namespace Perfumer\Component\Auth;

use Perfumer\Component\Auth\DataProvider\AbstractProvider as DataProvider;
use Perfumer\Component\Auth\TokenProvider\AbstractProvider as TokenProvider;

class Authentication
{
    /**
     * @var DataProvider
     */
    protected $data_provider;

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
    protected $is_authenticated = false;

    /**
     * @var bool
     */
    protected $is_processed = false;

    /**
     * @var string
     */
    protected $data;

    /**
     * @var string
     */
    protected $token;

    /**
     * @param Session $session
     * @param DataProvider $data_provider
     * @param TokenProvider $token_provider
     */
    public function __construct(Session $session, DataProvider $data_provider, TokenProvider $token_provider)
    {
        $this->session = $session;
        $this->data_provider = $data_provider;
        $this->token_provider = $token_provider;
    }

    /**
     * @return bool
     */
    public function isAuthenticated()
    {
        $this->authenticate();

        return $this->is_authenticated;
    }

    /**
     * @return string|null
     */
    public function getData()
    {
        $this->authenticate();

        return $this->data;
    }

    /**
     * @return string|null
     */
    public function getToken()
    {
        $this->authenticate();

        return $this->token;
    }

    /**
     * @param string $data
     * @return bool
     */
    public function startSession(string $data)
    {
        if ($this->isAuthenticated()) {
            return false;
        }

        $token = $this->session->generateId();

        $set = $this->data_provider->saveData($token, $data);

        if ($set) {
            $this->token = $token;
            $this->data = $data;
            $this->is_authenticated = true;

            $this->session->set($this->token, $this->data);

            $this->token_provider->setToken($this->token);
        }

        return $set;
    }

    /**
     * @return bool
     */
    public function logout()
    {
        if (!$this->isAuthenticated()) {
            return false;
        }

        $this->deleteToken($this->token);

        $this->data = null;
        $this->token = null;
        $this->is_authenticated = false;
        $this->is_processed = false;

        return true;
    }

    /**
     * @param array|null $tokens
     * @return bool
     */
    public function destroySessions($tokens = null)
    {
        if (!$this->isAuthenticated()) {
            return false;
        }

        if ($tokens === null) {
            $tokens = $this->data_provider->getTokens($this->data);
        }

        if (is_array($tokens)) {
            foreach ($tokens as $token) {
                $this->session->destroy($token);
            }
        }

        return true;
    }

    protected function authenticate()
    {
        if ($this->is_processed === true) {
            return;
        }

        $token = (string) $this->token_provider->getToken();

        if (!$token) {
            $this->is_processed = true;
            return;
        }

        if ($this->session->has($token)) {
            $data = $this->session->get($token);
        } else {
            $data = $this->data_provider->getData($token);

            if (!$data) {
                $this->deleteToken($token);
                $this->is_processed = true;
                return;
            }

            $this->session->set($token, $data);
        }

        $this->data = $data;
        $this->token = $token;
        $this->is_authenticated = true;
        $this->is_processed = true;

        $this->token_provider->setToken($token);
    }

    /**
     * @param string $token
     */
    protected function deleteToken(string $token)
    {
        if ($token) {
            $this->session->destroy($token);
            $this->token_provider->deleteToken();
            $this->data_provider->deleteToken($token);
        }
    }
}

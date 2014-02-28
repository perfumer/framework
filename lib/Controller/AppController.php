<?php

namespace Perfumer\Controller;

class AppController extends CoreController
{
    protected $auth;
    protected $session;
    protected $user;

    protected function before()
    {
        parent::before();

        $token_provider = $this->container->s('session.cookie_provider');
        $this->auth = $this->container->s('auth');
        $this->session = $this->container->s('session');

        $token = $token_provider->getToken();
        $this->session->start($token);
        $this->auth->init();
        $this->user = $this->auth->getUser();
    }
}
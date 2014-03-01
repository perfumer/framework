<?php

namespace Perfumer\Controller;

use Perfumer\Controller\Filter\PermissionPack;

class AppController extends CoreController
{
    use PermissionPack;

    protected $auth;
    protected $session;
    protected $user;

    protected function before()
    {
        parent::before();

        $this->filterActionExists($this->request->getAction(), 'html');

        $token_provider = $this->container->s('session.cookie_provider');
        $this->auth = $this->container->s('auth');
        $this->session = $this->container->s('session');

        $token = $token_provider->getToken();
        $this->session->start($token);
        $this->auth->init();
        $this->user = $this->auth->getUser();
    }
}
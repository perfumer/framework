<?php

namespace Perfumer\Controller;

class AppController extends CoreController
{
    protected $auth;
    protected $session;
    protected $assets;

    protected function before()
    {
        parent::before();

        $token_provider = $this->container->s('session.cookie_provider');
        $this->auth = $this->container->s('auth');
        $this->session = $this->container->s('session');
        $this->assets = $this->container->s('assets');

        $token = $token_provider->getToken();
        $this->session->start($token);
        $this->auth->init();
    }

    protected function after()
    {
        $this->assets
            ->addCSS($this->request->css)
            ->addJS($this->request->js);

        $this->addViewVars([
            'css' => $this->assets->getCSS(),
            'js' => $this->assets->getJS()
        ]);

        parent::after();
    }
}
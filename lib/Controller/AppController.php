<?php

namespace Perfumer\Controller;

class AppController extends CoreController
{
    protected $assets;

    protected function before()
    {
        parent::before();

        if (!method_exists($this, $this->request->getAction()))
            $this->proxy->forward('exception/html', 'pageNotFound');

        $this->assets = $this->container->s('assets');

        if (!$this->stock->has('user'))
        {
            $token = $this->container->s('session.cookie_provider')->getToken();

            $this->container->s('session')->start($token);
            $this->container->s('auth')->init();

            $this->stock->set('user', $this->container->s('auth')->getUser());
        }

        $this->user = $this->global_vars['user'] = $this->stock->get('user');
    }

    protected function after()
    {
        $this->assets
            ->addCSS($this->request->getCSS())
            ->addJS($this->request->getJS());

        $this->global_vars['css'] = $this->assets->getCSS();
        $this->global_vars['js'] = $this->assets->getJS();
        $this->global_vars['vars'] = $this->js_vars;

        parent::after();
    }
}
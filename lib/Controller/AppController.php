<?php

namespace Perfumer\Controller;

class AppController extends CoreController
{
    protected $assets;

    protected $js_vars = [];

    protected function before()
    {
        parent::before();

        if (!method_exists($this, $this->request->getAction()))
            $this->proxy->forward('exception/html', 'pageNotFound');

        $this->assets = $this->container->s('assets');
    }

    protected function after()
    {
        if ($this->render_template)
        {
            $this->assets
                ->addCss($this->request->getCss())
                ->addJs($this->request->getJs());

            $this->global_vars['css'] = $this->assets->getCss();
            $this->global_vars['js'] = $this->assets->getJs();
            $this->global_vars['vars'] = $this->js_vars;
        }

        parent::after();
    }

    protected function addJsVars(array $vars)
    {
        $this->js_vars = array_merge($this->js_vars, $vars);
    }

    protected function addJsVar($name, $value)
    {
        $this->js_vars[$name] = $value;
    }
}
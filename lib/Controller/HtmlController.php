<?php

namespace Perfumer\Controller;

class HtmlController extends CoreController
{
    protected $js_vars = [];

    protected function before()
    {
        parent::before();

        if (!method_exists($this, $this->request->getAction()))
            $this->proxy->forward('exception/html', 'pageNotFound');
    }

    protected function after()
    {
        $this->addViewVars([
            'vars' => $this->js_vars
        ]);

        parent::after();
    }

    protected function addJsVar($name, $value)
    {
        $this->js_vars[$name] = $value;
    }

    protected function addJsVars(array $vars)
    {
        $this->js_vars = array_merge($this->js_vars, $vars);
    }
}
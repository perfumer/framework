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
        if ($this->render_template)
        {
            if (!$this->template)
                $this->template = $this->request->getUrl() . '/' . $this->request->getAction();

            $this->addViewVars([
                'vars' => $this->js_vars
            ]);
        }

        parent::after();
    }

    protected function redirect($url)
    {
        $this->response->addHeader('Location', $url);
    }

    protected function addJsVars(array $vars)
    {
        $this->js_vars = array_merge($this->js_vars, $vars);
    }
}
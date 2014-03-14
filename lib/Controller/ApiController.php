<?php

namespace Perfumer\Controller;

class ApiController extends CoreController
{
    protected function before()
    {
        parent::before();

        if (!method_exists($this, $this->request->getAction()))
            $this->proxy->forward('exception/json', 'pageNotFound');
    }
}
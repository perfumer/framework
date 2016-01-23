<?php

namespace Perfumer\FrameworkBundle\Controller;

use Perfumer\Framework\Controller\PlainController;

class HttpController extends PlainController
{
    public function redirect($url, $status_code = 302)
    {
        $this->getExternalResponse()->setStatusCode($status_code)->headers->set('Location', $url);
    }
}

<?php

namespace Perfumer\FrameworkBundle\Controller;

use Perfumer\Framework\Controller\PlainController;

class RedirectController extends PlainController
{
    public function internal($url, $id = null, $query = [], $prefixes = [], $status_code = 302)
    {
        $this->getExternalResponse()->setStatusCode($status_code)->headers->set('Location', $this->getExternalRouter()->generateUrl($url, $id, $query, $prefixes));
    }

    public function external($url, $status_code = 302)
    {
        $this->getExternalResponse()->setStatusCode($status_code)->headers->set('Location', $url);
    }
}
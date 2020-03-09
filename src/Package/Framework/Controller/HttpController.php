<?php

namespace Perfumer\Package\Framework\Controller;

use Perfumer\Framework\Controller\PlainController;

class HttpController extends PlainController
{
    /**
     * @param string $url
     * @param int $status_code
     */
    public function redirect($url, $status_code = 302)
    {
        $this->getExternalResponse()->setStatusCode($status_code)->headers->set('Location', $url);
    }
}

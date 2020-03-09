<?php

namespace Perfumer\Package\Framework\Controller\Exception;

use Perfumer\Framework\Controller\AbstractController;

class PlainController extends AbstractController
{
    public function pageNotFound()
    {
        if ($this->getApplication()->getEnv() === 'http') {
            $this->getExternalResponse()->setStatusCode(404);
        }

        $this->getResponse()->setContent('Page not found');
    }

    public function isLogged()
    {
        if ($this->getApplication()->getEnv() === 'http') {
            $this->getExternalResponse()->setStatusCode(403);
        }

        $this->getResponse()->setContent('Access to this page is permitted to logged in users only.');
    }

    public function isAdmin()
    {
        if ($this->getApplication()->getEnv() === 'http') {
            $this->getExternalResponse()->setStatusCode(403);
        }

        $this->getResponse()->setContent('Access to this page is permitted to administrators only.');
    }

    public function isGranted()
    {
        if ($this->getApplication()->getEnv() === 'http') {
            $this->getExternalResponse()->setStatusCode(403);
        }

        $this->getResponse()->setContent('You do not have enough rights to access this page.');
    }
}

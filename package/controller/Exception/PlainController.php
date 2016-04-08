<?php

namespace Perfumer\Package\Controller\Exception;

use Perfumer\Framework\Controller\AbstractController;

class PlainController extends AbstractController
{
    public function pageNotFound()
    {
        if ($this->getRouter()->isHttp()) {
            $this->getExternalResponse()->setStatusCode(404);
        }

        $this->getResponse()->setContent('Page not found');
    }

    public function controllerNotFound()
    {
        if ($this->getRouter()->isHttp()) {
            $this->getExternalResponse()->setStatusCode(404);
        }

        $this->getResponse()->setContent('Controller not found');
    }

    public function isLogged()
    {
        if ($this->getRouter()->isHttp()) {
            $this->getExternalResponse()->setStatusCode(403);
        }

        $this->getResponse()->setContent('Access to this page is permitted to logged in users only.');
    }

    public function isAdmin()
    {
        if ($this->getRouter()->isHttp()) {
            $this->getExternalResponse()->setStatusCode(403);
        }

        $this->getResponse()->setContent('Access to this page is permitted to administrators only.');
    }

    public function isGranted()
    {
        if ($this->getRouter()->isHttp()) {
            $this->getExternalResponse()->setStatusCode(403);
        }

        $this->getResponse()->setContent('You do not have enough rights to access this page.');
    }

    protected function pageNotFoundException()
    {
    }

    protected function actionNotFoundException()
    {
    }
}

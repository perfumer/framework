<?php

namespace Perfumer\FrameworkPackage\Controller\Exception;

use Perfumer\Framework\Controller\PlainController as BaseController;

class PlainController extends BaseController
{
    public function pageNotFound()
    {
        if ($this->getExternalRouter()->isHttp())
            $this->getExternalResponse()->setStatusCode(404);

        $this->getResponse()->setContent('Page not found');
    }

    public function controllerNotFound()
    {
        if ($this->getExternalRouter()->isHttp())
            $this->getExternalResponse()->setStatusCode(404);

        $this->getResponse()->setContent('Controller not found');
    }

    public function actionNotFound()
    {
        if ($this->getExternalRouter()->isHttp())
            $this->getExternalResponse()->setStatusCode(404);

        $this->getResponse()->setContent('Action not found');
    }

    public function isLogged()
    {
        if ($this->getExternalRouter()->isHttp())
            $this->getExternalResponse()->setStatusCode(403);

        $this->getResponse()->setContent('Access to this page is permitted to logged in users only.');
    }

    public function isAdmin()
    {
        if ($this->getExternalRouter()->isHttp())
            $this->getExternalResponse()->setStatusCode(403);

        $this->getResponse()->setContent('Access to this page is permitted to administrators only.');
    }

    public function isGranted()
    {
        if ($this->getExternalRouter()->isHttp())
            $this->getExternalResponse()->setStatusCode(403);

        $this->getResponse()->setContent('You do not have enough rights to access this page.');
    }
}

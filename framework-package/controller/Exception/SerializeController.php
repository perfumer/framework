<?php

namespace Perfumer\FrameworkPackage\Controller\Exception;

use Perfumer\Framework\Controller\SerializeController as BaseController;

class SerializeController extends BaseController
{
    public function pageNotFound()
    {
        if ($this->getExternalRouter()->isHttp())
            $this->getExternalResponse()->setStatusCode(404);

        $this->setErrorMessage('Page not found.');
    }

    public function controllerNotFound()
    {
        if ($this->getExternalRouter()->isHttp())
            $this->getExternalResponse()->setStatusCode(404);

        $this->setErrorMessage('Controller not found.');
    }

    public function actionNotFound()
    {
        if ($this->getExternalRouter()->isHttp())
            $this->getExternalResponse()->setStatusCode(404);

        $this->setErrorMessage('Action not found.');
    }

    public function isLogged()
    {
        if ($this->getExternalRouter()->isHttp())
            $this->getExternalResponse()->setStatusCode(403);

        $this->setErrorMessage('Access to this page is permitted to logged in users only.');
    }

    public function isAdmin()
    {
        if ($this->getExternalRouter()->isHttp())
            $this->getExternalResponse()->setStatusCode(403);

        $this->setErrorMessage('Access to this page is permitted to administrators only.');
    }

    public function isGranted()
    {
        if ($this->getExternalRouter()->isHttp())
            $this->getExternalResponse()->setStatusCode(403);

        $this->setErrorMessage('You do not have enough rights to access this page.');
    }
}
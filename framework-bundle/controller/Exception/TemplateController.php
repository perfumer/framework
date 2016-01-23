<?php

namespace Perfumer\FrameworkBundle\Controller\Exception;

use Perfumer\Framework\Controller\TemplateController as BaseController;

class TemplateController extends BaseController
{
    public function pageNotFound()
    {
        if ($this->getExternalRouter()->isHttp())
            $this->getExternalResponse()->setStatusCode(404);
    }

    public function controllerNotFound()
    {
        if ($this->getExternalRouter()->isHttp())
            $this->getExternalResponse()->setStatusCode(404);
    }

    public function actionNotFound()
    {
        if ($this->getExternalRouter()->isHttp())
            $this->getExternalResponse()->setStatusCode(404);
    }

    public function isLogged()
    {
        if ($this->getExternalRouter()->isHttp())
            $this->getExternalResponse()->setStatusCode(403);
    }

    public function isAdmin()
    {
        if ($this->getExternalRouter()->isHttp())
            $this->getExternalResponse()->setStatusCode(403);
    }

    public function isGranted()
    {
        if ($this->getExternalRouter()->isHttp())
            $this->getExternalResponse()->setStatusCode(403);
    }
}

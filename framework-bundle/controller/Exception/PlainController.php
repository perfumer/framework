<?php

namespace Perfumer\FrameworkBundle\Controller\Exception;

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
}

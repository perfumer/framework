<?php

namespace Perfumer\FrameworkBundle\Controller\Exception;

use Perfumer\Framework\Controller\TemplateController as BaseController;

class TemplateController extends BaseController
{
    protected $_is_http_request = false;

    protected function before()
    {
        parent::before();

        $this->_is_http_request = ($this->getExternalRouter()->getName() === 'http_router');
    }

    public function pageNotFound()
    {
        if ($this->_is_http_request)
            $this->getExternalResponse()->setStatusCode(404);
    }

    public function controllerNotFound()
    {
        if ($this->_is_http_request)
            $this->getExternalResponse()->setStatusCode(404);
    }

    public function actionNotFound()
    {
        if ($this->_is_http_request)
            $this->getExternalResponse()->setStatusCode(405);
    }

    public function isLogged()
    {
        if ($this->_is_http_request)
            $this->getExternalResponse()->setStatusCode(403);
    }

    public function isAdmin()
    {
        if ($this->_is_http_request)
            $this->getExternalResponse()->setStatusCode(403);
    }

    public function isGranted()
    {
        if ($this->_is_http_request)
            $this->getExternalResponse()->setStatusCode(403);
    }
}
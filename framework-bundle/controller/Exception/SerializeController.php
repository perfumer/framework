<?php

namespace Perfumer\FrameworkBundle\Controller\Exception;

use Perfumer\Framework\Controller\SerializeController as BaseController;

class SerializeController extends BaseController
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

        $this->setErrorMessage('Page not found.');
    }

    public function actionNotFound()
    {
        if ($this->_is_http_request)
            $this->getExternalResponse()->setStatusCode(405);

        $this->setErrorMessage('Action not found.');
    }

    public function isLogged()
    {
        if ($this->_is_http_request)
            $this->getExternalResponse()->setStatusCode(403);

        $this->setErrorMessage('Access to this page is permitted to logged in users only.');
    }

    public function isAdmin()
    {
        if ($this->_is_http_request)
            $this->getExternalResponse()->setStatusCode(403);

        $this->setErrorMessage('Access to this page is permitted to administrators only.');
    }

    public function isGranted()
    {
        if ($this->_is_http_request)
            $this->getExternalResponse()->setStatusCode(403);

        $this->setErrorMessage('You do not have enough rights to access this page.');
    }
}
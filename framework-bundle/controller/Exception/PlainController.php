<?php

namespace Perfumer\FrameworkBundle\Controller\Exception;

use Perfumer\Framework\Controller\PlainController as BaseController;

class PlainController extends BaseController
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

        $this->getResponse()->setContent('Page not found');
    }

    public function controllerNotFound()
    {
        if ($this->_is_http_request)
            $this->getExternalResponse()->setStatusCode(404);

        $this->getResponse()->setContent('Controller not found');
    }

    public function actionNotFound()
    {
        if ($this->_is_http_request)
            $this->getExternalResponse()->setStatusCode(405);

        $this->getResponse()->setContent('Action not found');
    }
}
<?php

namespace Perfumer\Package\Controller\Exception;

use Perfumer\Framework\Controller\SerializeController as BaseController;
use Perfumer\Framework\View\SerializeView;

class SerializeController extends BaseController
{
    public function pageNotFound()
    {
        if ($this->getExternalRouter()->isHttp()) {
            $this->getExternalResponse()->setStatusCode(404);
        }

        $this->setErrorMessage('Page not found.');
    }

    public function controllerNotFound()
    {
        if ($this->getExternalRouter()->isHttp()) {
            $this->getExternalResponse()->setStatusCode(404);
        }

        $this->setErrorMessage('Controller not found.');
    }

    public function isLogged()
    {
        if ($this->getExternalRouter()->isHttp()) {
            $this->getExternalResponse()->setStatusCode(403);
        }

        $this->setErrorMessage('Access to this page is permitted to logged in users only.');
    }

    public function isAdmin()
    {
        if ($this->getExternalRouter()->isHttp()) {
            $this->getExternalResponse()->setStatusCode(403);
        }

        $this->setErrorMessage('Access to this page is permitted to administrators only.');
    }

    public function isGranted()
    {
        if ($this->getExternalRouter()->isHttp()) {
            $this->getExternalResponse()->setStatusCode(403);
        }

        $this->setErrorMessage('You do not have enough rights to access this page.');
    }

    /**
     * @return SerializeView
     */
    protected function getView()
    {
        if ($this->_view === null) {
            $this->_view = $this->s('view.serialize');
        }

        return $this->_view;
    }
}

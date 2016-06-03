<?php

namespace Perfumer\Package\Controller\Exception;

use Perfumer\Framework\Controller\ViewController;
use Perfumer\Framework\Router\Http\StatusViewControllerHelpers;
use Perfumer\Framework\View\StatusView;

class StatusController extends ViewController
{
    use StatusViewControllerHelpers;

    public function pageNotFound()
    {
        if ($this->getRouter()->isHttp()) {
            $this->getExternalResponse()->setStatusCode(404);
        }

        $this->setErrorMessage('Page not found.');
    }

    public function isLogged()
    {
        if ($this->getRouter()->isHttp()) {
            $this->getExternalResponse()->setStatusCode(403);
        }

        $this->setErrorMessage('Access to this page is permitted to logged in users only.');
    }

    public function isAdmin()
    {
        if ($this->getRouter()->isHttp()) {
            $this->getExternalResponse()->setStatusCode(403);
        }

        $this->setErrorMessage('Access to this page is permitted to administrators only.');
    }

    public function isGranted()
    {
        if ($this->getRouter()->isHttp()) {
            $this->getExternalResponse()->setStatusCode(403);
        }

        $this->setErrorMessage('You do not have enough rights to access this page.');
    }

    /**
     * @return StatusView
     */
    protected function getView()
    {
        if ($this->_view === null) {
            $this->_view = $this->s('view.status');
        }

        return $this->_view;
    }
}

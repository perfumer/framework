<?php

namespace Perfumer\Package\Framework\Command;

use Perfumer\Framework\Controller\PlainController;

class ExceptionCommand extends PlainController
{
    public function pageNotFound()
    {
        $this->getResponse()->setContent('Page not found');
    }

    public function isLogged()
    {
        $this->getResponse()->setContent('Access to this page is permitted to logged in users only.');
    }

    public function isAdmin()
    {
        $this->getResponse()->setContent('Access to this page is permitted to administrators only.');
    }

    public function isGranted()
    {
        $this->getResponse()->setContent('You do not have enough rights to access this page.');
    }
}

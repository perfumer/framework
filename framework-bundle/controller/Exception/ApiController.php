<?php

namespace Perfumer\FrameworkBundle\Controller\Exception;

use Perfumer\Framework\Controller\SerializeController;

class ApiController extends SerializeController
{
    public function apiSecretRequired()
    {
        $this->getExternalResponse()->setStatusCode(403);

        $this->setErrorMessage('You are required to provide application secret key to get access to API.');
    }

    public function apiSecretInvalid()
    {
        $this->getExternalResponse()->setStatusCode(403);

        $this->setErrorMessage('Invalid application secret key.');
    }
}
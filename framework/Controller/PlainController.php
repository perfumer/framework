<?php

namespace Perfumer\Framework\Controller;

class PlainController extends AbstractController
{
    protected function pageNotFoundException()
    {
        $this->getProxy()->forward('framework', 'exception/plain', 'pageNotFound');
    }

    protected function actionNotFoundException()
    {
        $this->getProxy()->forward('framework', 'exception/plain', 'actionNotFound');
    }
}
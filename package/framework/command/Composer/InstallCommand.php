<?php

namespace Perfumer\Package\Framework\Command\Composer;

use Perfumer\Framework\Controller\PlainController;

class InstallCommand extends PlainController
{
    public function action()
    {
        $this->doAction();
    }

    public function doAction()
    {
        shell_exec('php composer.phar install --prefer-dist');
    }
}

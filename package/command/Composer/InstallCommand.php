<?php

namespace Perfumer\Package\Command\Composer;

use Perfumer\Framework\Controller\PlainController;

class InstallCommand extends PlainController
{
    public function action()
    {
        shell_exec('php composer.phar install --prefer-dist');
    }
}

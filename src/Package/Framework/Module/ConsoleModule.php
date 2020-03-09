<?php

namespace Perfumer\Package\Framework\Module;

use Perfumer\Framework\Controller\Module;

class ConsoleModule extends Module
{
    public $name = 'framework';

    public $router = 'router.console';

    public $request = 'package.framework.console_request';
}

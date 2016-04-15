<?php

namespace Perfumer\Package\Command\Propel\Migration;

use Perfumer\Framework\Controller\PlainController;

class DownCommand extends PlainController
{
    public function action()
    {
        $platform = $this->getContainer()->getParam('propel/platform');
        $config_dir = $this->getContainer()->getParam('propel/config_dir');
        $migration_dir = $this->getContainer()->getParam('propel/migration_dir');

        echo shell_exec(join(' ', [
            'vendor/bin/propel migration:down',
            '--platform=' . $platform,
            '--config-dir=' . $config_dir,
            '--output-dir=' . $migration_dir
        ]));
    }
}
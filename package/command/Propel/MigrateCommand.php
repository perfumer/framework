<?php

namespace Perfumer\Package\Command\Propel;

use Perfumer\Framework\Controller\PlainController;

class MigrateCommand extends PlainController
{
    public function action()
    {
        $this->doAction();
    }

    public function doAction()
    {
        $bin = $this->getContainer()->getParam('propel/bin');
        $platform = $this->getContainer()->getParam('propel/platform');
        $config_dir = $this->getContainer()->getParam('propel/config_dir');
        $migration_dir = $this->getContainer()->getParam('propel/migration_dir');

        echo shell_exec(join(' ', [
            $bin . ' migrate',
            '--platform=' . $platform,
            '--config-dir=' . $config_dir,
            '--output-dir=' . $migration_dir,
        ]));
    }
}

<?php

namespace Perfumer\Package\Command\Propel;

use Perfumer\Framework\Controller\PlainController;

class DiffCommand extends PlainController
{
    public function action ()
    {
        $platform = $this->getContainer()->getParam('propel/platform');
        $config_dir = $this->getContainer()->getParam('propel/config_dir');
        $migration_dir = $this->getContainer()->getParam('propel/migration_dir');
        $schema_dir = $this->getContainer()->getParam('propel/schema_dir');

        echo shell_exec(join(' ', [
            'vendor/bin/propel diff',
            '--platform=' . $platform,
            '--schema-dir=' . $schema_dir,
            '--config-dir=' . $config_dir,
            '--output-dir=' . $migration_dir,
            '--recursive'
        ]));
    }
}

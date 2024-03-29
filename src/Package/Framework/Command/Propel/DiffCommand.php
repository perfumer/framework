<?php

namespace Perfumer\Package\Framework\Command\Propel;

use Perfumer\Framework\Controller\PlainController;

class DiffCommand extends PlainController
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
        $migration_table = $this->getContainer()->getParam('propel/migration_table');
        $schema_dir = $this->getContainer()->getParam('propel/schema_dir');

        exec(join(' ', [
            $bin . ' diff',
            '--platform=' . $platform,
            '--schema-dir=' . $schema_dir,
            '--config-dir=' . $config_dir,
            '--output-dir=' . $migration_dir,
            '--migration-table=' . $migration_table,
            '--recursive'
        ]), $output, $result_code);

        echo implode("\n", $output);

        if ($result_code !== 0) {
            exit(1);
        }
    }
}

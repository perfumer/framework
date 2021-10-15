<?php

namespace Perfumer\Package\Framework\Command\Propel\Migration;

use Perfumer\Framework\Controller\PlainController;

class DownCommand extends PlainController
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

        exec(join(' ', [
            $bin . ' migration:down',
            '--platform=' . $platform,
            '--config-dir=' . $config_dir,
            '--output-dir=' . $migration_dir,
            '--migration-table=' . $migration_table,
        ]), $output, $result_code);

        echo implode("\n", $output);

        if ($result_code !== 0) {
            exit(1);
        }
    }
}
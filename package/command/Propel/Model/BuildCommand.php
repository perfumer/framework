<?php

namespace Perfumer\Package\Command\Propel\Model;

use Perfumer\Framework\Controller\PlainController;

class BuildCommand extends PlainController
{
    public function action()
    {
        $this->doAction();
    }

    public function doAction()
    {
        $platform = $this->getContainer()->getParam('propel/platform');
        $config_dir = $this->getContainer()->getParam('propel/config_dir');
        $model_dir = $this->getContainer()->getParam('propel/model_dir');
        $schema_dir = $this->getContainer()->getParam('propel/schema_dir');

        echo shell_exec(join(' ', [
            'vendor/bin/propel model:build',
            '--platform=' . $platform,
            '--schema-dir=' . $schema_dir,
            '--config-dir=' . $config_dir,
            '--output-dir=' . $model_dir,
            '--disable-namespace-auto-package',
            '--recursive'
        ]));
    }
}

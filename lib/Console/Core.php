<?php

namespace Perfumer\Console;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;

class Core extends Application
{
    protected function getCommandName(InputInterface $input)
    {
        return 'single_command';
    }

    protected function getDefaultCommands()
    {
        $defaultCommands = parent::getDefaultCommands();

        $defaultCommands[] = new SingleCommand();

        return $defaultCommands;
    }

    public function getDefinition()
    {
        $inputDefinition = parent::getDefinition();

        $inputDefinition->setArguments();

        return $inputDefinition;
    }
}
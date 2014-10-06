<?php

namespace Perfumer\Console;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;

class Application extends BaseApplication
{
    /**
     * @var SingleApplicationCommand
     */
    protected $single_application_command;

    public function __construct(SingleApplicationCommand $single_application_command)
    {
        $this->single_application_command = $single_application_command;

        parent::__construct();
    }

    protected function getCommandName(InputInterface $input)
    {
        return 'single_application_command';
    }

    protected function getDefaultCommands()
    {
        $defaultCommands = parent::getDefaultCommands();

        $defaultCommands[] = $this->single_application_command;

        return $defaultCommands;
    }

    public function getDefinition()
    {
        $inputDefinition = parent::getDefinition();

        $inputDefinition->setArguments();

        return $inputDefinition;
    }
}
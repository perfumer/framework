<?php

namespace Perfumer\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SingleApplicationCommand extends Command
{
    /**
     * @var Proxy
     */
    protected $proxy;

    public function __construct(Proxy $proxy)
    {
        $this->proxy = $proxy;

        parent::__construct();
    }

    protected function configure()
    {
        $this->ignoreValidationErrors();

        $this
            ->setName('single_application_command')
            ->addArgument('url', InputArgument::REQUIRED)
            ->addArgument('args', InputArgument::IS_ARRAY);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $url = $input->getArgument('url');

        $this->proxy->init($url, $input, $output)->start();
    }
}
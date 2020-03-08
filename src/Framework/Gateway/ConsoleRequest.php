<?php

namespace Perfumer\Framework\Gateway;

class ConsoleRequest
{
    /**
     * @var array
     */
    private $argv = [];

    /**
     * @return array
     */
    public function getArgv(): array
    {
        return $this->argv;
    }

    /**
     * @param array $argv
     */
    public function setArgv(array $argv): void
    {
        $this->argv = $argv;
    }
}
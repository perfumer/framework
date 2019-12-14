<?php

namespace Perfumer\Framework\Controller;

class Module
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $container;

    /**
     * @var bool
     */
    public $is_container_reachable = true;

    /**
     * @var string
     */
    public $router;

    /**
     * @var string
     */
    public $request;

    /**
     * @var string
     */
    public $response = 'response';

    /**
     * @var array
     */
    protected $components = [];

    /**
     * @param string $name
     * @return string
     */
    public function getComponent(string $name): ?string
    {
        return $this->components[$name] ?? null;
    }

    /**
     * @return array
     */
    public function getComponents(): array
    {
        return $this->components;
    }
}

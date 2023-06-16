<?php

namespace Perfumer\Framework\Proxy;

class Attributes
{
    protected string $module;

    protected string $resource;

    protected string $action;

    protected array $args;

    public function __construct(string $module, string $resource, string $action, array $args = [])
    {
        $this->module = (string) $module;
        $this->resource = (string) $resource;
        $this->action = (string) $action;
        $this->args = (array) $args;
    }

    public function getIdentity(): string
    {
        return $this->module . '.' . $this->resource . '.' . $this->action;
    }

    /**
     * @deprecated Use getModule() instead
     */
    public function getBundle(): string
    {
        return $this->module;
    }

    public function getModule(): string
    {
        return $this->module;
    }

    public function getResource(): string
    {
        return $this->resource;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getArgs(): array
    {
        return $this->args;
    }
}

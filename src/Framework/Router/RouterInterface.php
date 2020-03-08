<?php

namespace Perfumer\Framework\Router;

interface RouterInterface
{
    /**
     * @return array
     */
    public function getAllowedActions(): array;

    /**
     * @return array
     */
    public function getNotFoundAttributes(): array;

    /**
     * @return bool
     * @deprecated Use $this->getApplication()->getEnv() instead
     */
    public function isHttp(): bool;

    /**
     * @param $request
     * @return array
     */
    public function dispatch($request): array;
}

<?php

namespace Perfumer\Framework\InternalRouter;

interface RouterInterface
{
    public function dispatch($url);
}
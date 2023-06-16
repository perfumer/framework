<?php

namespace Perfumer\Component\Endpoint\Attributes;

#[\Attribute(\Attribute::TARGET_METHOD)]
class Api extends Attribute
{
    public function __construct(
        public string $path,
        public string $group,
        public string $name,
        public ?string $version = null,
        public ?string $desc = null,
    )
    {

    }
}
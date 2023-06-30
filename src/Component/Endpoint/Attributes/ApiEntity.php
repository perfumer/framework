<?php

namespace Perfumer\Component\Endpoint\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS|\Attribute::TARGET_METHOD)]
class ApiEntity extends Attribute
{
    public function __construct(
        public ?string $desc = null,
    )
    {

    }
}
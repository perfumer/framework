<?php

namespace Perfumer\Component\Endpoint\Attributes;

#[\Attribute(\Attribute::TARGET_METHOD)]
class SameAs extends Attribute
{
    public function __construct(
        public string $endpoint
    )
    {
    }
}
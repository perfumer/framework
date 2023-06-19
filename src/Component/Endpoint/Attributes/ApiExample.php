<?php

namespace Perfumer\Component\Endpoint\Attributes;

#[\Attribute(\Attribute::TARGET_METHOD|\Attribute::IS_REPEATABLE)]
class ApiExample extends Attribute
{
    public string $apidocAnnotation = 'apiExample';

    public function __construct(
        public ?string $desc = null,
        public array $json,
    )
    {

    }
}
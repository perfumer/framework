<?php

namespace Perfumer\Component\Endpoint\Attributes;

#[\Attribute(\Attribute::TARGET_METHOD|\Attribute::IS_REPEATABLE)]
class ApiErrorExample extends ApiExample
{
    public string $apidocAnnotation = 'apiErrorExample';

    public function __construct(
        public ?string $desc = null,
        public array $json,
    )
    {

    }
}
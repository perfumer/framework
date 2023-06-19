<?php

namespace Perfumer\Component\Endpoint\Attributes;

#[\Attribute(\Attribute::TARGET_METHOD|\Attribute::IS_REPEATABLE)]
class ApiSuccessExample extends ApiExample
{
    public string $apidocAnnotation = 'apiSuccessExample';

    public function __construct(
        public ?string $desc = null,
        public array $json,
    )
    {

    }
}
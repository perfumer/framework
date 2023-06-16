<?php

namespace Perfumer\Component\Endpoint\Attributes;

#[\Attribute(\Attribute::TARGET_METHOD|\Attribute::IS_REPEATABLE)]
class ApiParamExample extends ApiExample
{
    public string $apidocAnnotation = 'ApiParamExample';

    public function __construct(
        public ?string $desc = null,
        public array $json,
    )
    {

    }
}
<?php

namespace Perfumer\Component\Endpoint\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS|\Attribute::TARGET_METHOD|\Attribute::IS_REPEATABLE)]
class Entity extends Obj
{
    public function __construct(
        public string $name = '',
        public bool $required = false,
        public bool $arr = false,
        public string $desc = ''
    )
    {

    }
}
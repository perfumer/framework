<?php

namespace Perfumer\Component\Endpoint\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS|\Attribute::TARGET_METHOD|\Attribute::IS_REPEATABLE)]
class Name extends Str
{
    public function fake(): mixed
    {
        return $this->arr ? ['John'] : 'John';
    }
}
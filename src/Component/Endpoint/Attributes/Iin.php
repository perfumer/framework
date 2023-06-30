<?php

namespace Perfumer\Component\Endpoint\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS|\Attribute::TARGET_METHOD|\Attribute::IS_REPEATABLE)]
class Iin extends Str
{
    public function fake(): mixed
    {
        return $this->arr ? ['111222333444'] : '111222333444';
    }
}
<?php

namespace Perfumer\Component\Endpoint\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS|\Attribute::TARGET_METHOD|\Attribute::IS_REPEATABLE)]
class Phone extends Str
{
    public function fake(): mixed
    {
        return $this->arr ? ['+71234567890'] : '+71234567890';
    }
}
<?php

namespace Perfumer\Component\Endpoint\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS|\Attribute::TARGET_METHOD|\Attribute::IS_REPEATABLE)]
class Time extends Str
{
    public function fake(): mixed
    {
        return $this->arr ? [date('H:i:s')] : date('H:i:s');
    }
}
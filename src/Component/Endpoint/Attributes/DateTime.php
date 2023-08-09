<?php

namespace Perfumer\Component\Endpoint\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS|\Attribute::TARGET_METHOD|\Attribute::IS_REPEATABLE)]
class DateTime extends Str
{
    public function fake(): mixed
    {
        return $this->arr ? [date('Y-m-d H:i:s')] : date('Y-m-d H:i:s');
    }
}
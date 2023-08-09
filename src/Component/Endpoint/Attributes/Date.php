<?php

namespace Perfumer\Component\Endpoint\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS|\Attribute::TARGET_METHOD|\Attribute::IS_REPEATABLE)]
class Date extends Str
{
    public function fake(): mixed
    {
        return $this->arr ? [date('Y-m-d')] : date('Y-m-d');
    }
}
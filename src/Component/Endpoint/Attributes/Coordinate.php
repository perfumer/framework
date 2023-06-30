<?php

namespace Perfumer\Component\Endpoint\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS|\Attribute::TARGET_METHOD|\Attribute::IS_REPEATABLE)]
class Coordinate extends Double
{
    public function fake(): mixed
    {
        return $this->arr ? [12.95456] : 12.95456;
    }
}
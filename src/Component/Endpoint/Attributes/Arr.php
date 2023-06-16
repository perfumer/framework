<?php

namespace Perfumer\Component\Endpoint\Attributes;

#[\Attribute(\Attribute::TARGET_METHOD|\Attribute::IS_REPEATABLE)]
class Arr extends Type
{
    public string $type = 'array';

    public function validate(mixed $value): ?string
    {
        return is_array($value) ? null : sprintf('%s is not an array', $this->name);
    }

    public function fake(): mixed
    {
        return [[]];
    }
}
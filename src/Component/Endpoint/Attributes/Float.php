<?php

namespace Perfumer\Component\Endpoint\Attributes;

#[\Attribute(\Attribute::TARGET_METHOD|\Attribute::IS_REPEATABLE)]
class Float extends Type
{
    public string $type = 'float';

    public function validate(mixed $value): ?string
    {
        return is_float($value) ? null : sprintf('%s is not a float', $this->name);
    }

    public function fake(): mixed
    {
        return 0.95;
    }
}
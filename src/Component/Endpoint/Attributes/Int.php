<?php

namespace Perfumer\Component\Endpoint\Attributes;

#[\Attribute(\Attribute::TARGET_METHOD|\Attribute::IS_REPEATABLE)]
class Int extends Type
{
    public string $type = 'int';

    public function validate(mixed $value): ?string
    {
        return is_int($value) ? null : sprintf('%s is not an integer', $this->name);
    }

    public function fake(): mixed
    {
        return 123;
    }
}
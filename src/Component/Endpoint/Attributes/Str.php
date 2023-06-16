<?php

namespace Perfumer\Component\Endpoint\Attributes;

#[\Attribute(\Attribute::TARGET_METHOD|\Attribute::IS_REPEATABLE)]
class Str extends Type
{
    public string $type = 'string';

    public function validate(mixed $value): ?string
    {
        return is_string($value) ? null : sprintf('%s is not a string', $this->name);
    }

    public function fake(): mixed
    {
        return 'Lorem ipsum';
    }
}
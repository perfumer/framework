<?php

namespace Perfumer\Component\Endpoint\Attributes;

#[\Attribute(\Attribute::TARGET_METHOD|\Attribute::IS_REPEATABLE)]
class Bool extends Type
{
    public string $type = 'bool';

    public function validate(mixed $value): ?string
    {
        return is_bool($value) ? null : sprintf('%s is not a bool', $this->name);
    }

    public function fake(): mixed
    {
        return true;
    }
}
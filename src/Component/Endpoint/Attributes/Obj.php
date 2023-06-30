<?php

namespace Perfumer\Component\Endpoint\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS|\Attribute::TARGET_METHOD|\Attribute::IS_REPEATABLE)]
class Obj extends Type
{
    public string $type = 'object';

    public function validate(mixed $value): ?string
    {
        return is_array($value) ? null : sprintf('%s is not an array', $this->name);
    }

    public function fake(): mixed
    {
        return $this->arr ? [new \stdClass()] : new \stdClass();
    }
}
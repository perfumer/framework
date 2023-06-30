<?php

namespace Perfumer\Component\Endpoint\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS|\Attribute::TARGET_METHOD|\Attribute::IS_REPEATABLE)]
class Integer extends Type
{
    public string $type = 'int';

    public function validate(mixed $value): ?string
    {
        if ($this->arr) {
            if (!is_array($value)) {
                return sprintf('%s is not an array of integers', $this->name);
            }

            foreach ($value as $item) {
                if (!is_int($item)) {
                    return sprintf('%s is not an array of integers', $this->name);
                }
            }
        } else {
            return is_int($value) ? null : sprintf('%s is not an integer', $this->name);
        }

        return null;
    }

    public function fake(): mixed
    {
        return $this->arr ? [123] : 123;
    }
}
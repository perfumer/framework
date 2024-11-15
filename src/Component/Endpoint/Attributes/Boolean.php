<?php

namespace Perfumer\Component\Endpoint\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS|\Attribute::TARGET_METHOD|\Attribute::IS_REPEATABLE)]
class Boolean extends Type
{
    public string $type = 'bool';

    public function validate(mixed $value): ?string
    {
        if ($this->arr) {
            if (!is_array($value)) {
                return sprintf('%s is not an array of booleans', $this->name);
            }

            foreach ($value as $item) {
                if (!is_bool($item)) {
                    return sprintf('%s is not an array of booleans', $this->name);
                }
            }
        } else {
            return is_bool($value) ? null : sprintf('%s is not a boolean', $this->name);
        }

        return null;
    }

    public function fake(): mixed
    {
        return $this->arr ? [true] : true;
    }
}
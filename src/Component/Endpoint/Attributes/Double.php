<?php

namespace Perfumer\Component\Endpoint\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS|\Attribute::TARGET_METHOD|\Attribute::IS_REPEATABLE)]
class Double extends Type
{
    public string $type = 'double';

    public function validate(mixed $value): ?string
    {
        if ($this->arr) {
            if (!is_array($value)) {
                return sprintf('%s is not an array of floats', $this->name);
            }

            foreach ($value as $item) {
                if (!is_float($item)) {
                    return sprintf('%s is not an array of floats', $this->name);
                }
            }
        } else {
            return is_float($value) ? null : sprintf('%s is not a float', $this->name);
        }

        return null;
    }

    public function fake(): mixed
    {
        return $this->arr ? [0.95] : 0.95;
    }
}
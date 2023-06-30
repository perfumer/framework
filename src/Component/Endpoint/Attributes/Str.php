<?php

namespace Perfumer\Component\Endpoint\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS|\Attribute::TARGET_METHOD|\Attribute::IS_REPEATABLE)]
class Str extends Type
{
    public string $type = 'string';

    public function validate(mixed $value): ?string
    {
        if ($this->arr) {
            if (!is_array($value)) {
                return sprintf('%s is not an array of strings', $this->name);
            }

            foreach ($value as $item) {
                if (!is_string($item)) {
                    return sprintf('%s is not an array of strings', $this->name);
                }
            }
        } else {
            return is_string($value) ? null : sprintf('%s is not a string', $this->name);
        }

        return null;
    }

    public function fake(): mixed
    {
        return $this->arr ? ['Lorem ipsum'] : 'Lorem ipsum';
    }
}
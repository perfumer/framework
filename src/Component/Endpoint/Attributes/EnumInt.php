<?php

namespace Perfumer\Component\Endpoint\Attributes;

use function React\Promise\all;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class EnumInt extends Type
{
    public string $type = 'int';

    public function __construct(
        public string $name,
        public array $allowedValues,
        public bool $required = false,
        public bool $arr = false,
        public string $desc = ''
    )
    {
        parent::__construct(
            name: $name,
            required: $required,
            arr: $arr,
            desc: $desc
        );
    }

    public static function fromArray(array $array): static
    {
        return new static(
            name: $array['name'],
            allowedValues: (array) $array['allowedValues'],
            required: (bool) ($array['required'] ?? false),
            arr: (bool) ($array['arr'] ?? false),
            desc: $array['desc'] ?? '',
        );
    }

    public function validate(mixed $value): ?string
    {
        if (count($this->allowedValues) === 0) {
            return sprintf('Allowed values are not set for %s', $this->name);
        }

        if ($this->arr) {
            if (!is_array($value)) {
                return sprintf('%s is not an array of integers', $this->name);
            }

            foreach ($value as $item) {
                if (!in_array($item, $this->allowedValues, true)) {
                    return sprintf('%s is not one of: %s', $this->name, join(', ', $this->allowedValues));
                }
            }
        } else {
            return in_array($value, $this->allowedValues, true) ?
                null :
                sprintf('%s is not one of: %s', $this->name, join(', ', $this->allowedValues));
        }

        return null;
    }

    public function fake(): mixed
    {
        return $this->arr ? [$this->allowedValues[0]] : $this->allowedValues[0];
    }
}
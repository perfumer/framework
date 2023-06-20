<?php

namespace Perfumer\Component\Endpoint\Attributes;

class Type extends Attribute
{
    public string $type;

    public function __construct(
        public string $name,
        public bool $required = false,
        public bool $arr = false,
        public string $desc = ''
    )
    {

    }

    public static function fromArray(array $array): static
    {
        return new static(
            name: $array['name'],
            required: (bool) ($array['required'] ?? false),
            arr: (bool) ($array['arr'] ?? false),
            desc: $array['desc'] ?? '',
        );
    }

    public function validate(mixed $value): ?string
    {
        return null;
    }

    public function fake(): mixed
    {

    }
}
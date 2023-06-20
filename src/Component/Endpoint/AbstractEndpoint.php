<?php

namespace Perfumer\Component\Endpoint;

use Perfumer\Component\Endpoint\Attributes\Arr;
use Perfumer\Component\Endpoint\Attributes\Attribute;

abstract class AbstractEndpoint
{
    protected array $in = [];
    protected array $out = [];
    protected bool $isInValidated = false;
    protected bool $isOutValidated = false;
    protected array $inErrors = [];
    protected array $outErrors = [];

    public function validateIn(string $method, array $data): ?array
    {
        if ($this->isInValidated) {
            return $this->inErrors;
        }

        $allErrors = [];

        if (isset($this->in[$method])) {
            $allErrors = $this->validate($this->in[$method], $data);
        }

        $this->isInValidated = true;
        $this->inErrors = $allErrors;

        return $allErrors;
    }

    public function validateOut(string $method, array $data): ?array
    {
        if ($this->isOutValidated) {
            return $this->outErrors;
        }

        $allErrors = [];

        if (isset($this->out[$method])) {
            $allErrors = $this->validate($this->out[$method], $data);
        }

        $this->isOutValidated = true;
        $this->outErrors = $allErrors;

        return $allErrors;
    }

    public function fake(string $method): array
    {
        $return = [];

        if (isset($this->out[$method])) {
            /** @var Attribute $attribute */
            foreach ($this->out[$method] as $attribute) {
                $this->setValueToNestedArray($attribute->name, $attribute->fake(), $return);
            }
        }

        return $return;
    }

    /**
     * @param Attribute[] $attributes
     */
    private function validate(array $attributes, array $data): ?array
    {
        $allErrors = [];
        $arrayKeys = [];

        foreach ($attributes as $attribute) {
            if ($attribute->arr) {
                $arrayKeys[] = $attribute->name;
            }

            $value = $this->getValueFromNestedArray($data, $attribute->name, $arrayKeys);
            $errors = [];
            $isEmpty = $value === null || $value === '';

            if ($attribute->required && $isEmpty) {
                $errors[] = sprintf('%s is required', $attribute->name);
            }

            if (!$isEmpty) {
                $error = $attribute->validate($value);

                if ($error) {
                    $errors[] = $error;
                }
            }

            if ($errors) {
                $allErrors[$attribute->name] = join(', ', $errors);
            }
        }

        return $allErrors;
    }

    private function getValueFromNestedArray(array $array, string $string, array $arrayKeys): mixed
    {
        $keys = explode('.', $string);
        $value = $array;
        $prefix = '';

        foreach ($keys as $key) {
            if (!is_array($value)) {
                return null;
            }

            if (in_array($prefix, $arrayKeys)) {
                if (isset($value[0])) {
                    $value = $value[0];
                } else {
                    return null;
                }
            }

            if (is_array($value) && isset($value[$key])) {
                $value = $value[$key];
            } else {
                return null;
            }

            $prefix .= $prefix ? '.'.$key : $key;
        }

        return $value;
    }

    private function setValueToNestedArray($string, $value, &$array): void
    {
        $keys = explode('.', $string);
        $nestedArray = &$array;

        foreach ($keys as $key) {
            if (is_array($nestedArray) && !isset($nestedArray[$key])) {
                $nestedArray[$key] = [];
            } elseif (is_object($nestedArray) && !property_exists($nestedArray, $key)) {
                $nestedArray->$key = new \stdClass();
            }

            if (is_array($nestedArray)) {
                $nestedArray = &$nestedArray[$key];
            } elseif (is_object($nestedArray)) {
                $nestedArray = &$nestedArray->$key;
            }

            if (is_array($nestedArray) && isset($nestedArray[0])) {
                $nestedArray = &$nestedArray[0];
            }
        }

        $nestedArray = $value;
    }
}
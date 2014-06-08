<?php

namespace Perfumer\Helper;

class Arr
{
    public function fetch(array $array, array $keys)
    {
        $keys = array_fill_keys($keys, true);
        return array_intersect_key($array, $keys);
    }

    public function trim(array $array, $char = ' ')
    {
        foreach ($array as &$value)
            $value = is_array($value) ? $this->trim($value, $char) : trim($value, $char);

        return $array;
    }

    public function convertValues(array $array, $from_value, $to_value)
    {
        foreach ($array as &$value)
        {
            if (is_array($value))
            {
                $value = $this->convertValues($value, $from_value, $to_value);
            }
            else
            {
                if ($value === $from_value)
                    $value = $to_value;
            }
        }

        return $array;
    }
}
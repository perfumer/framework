<?php

namespace Perfumer\Helper;

class Arr
{
    public function fetch(array $array, array $keys)
    {
        $result = [];

        foreach ($keys as $key)
        {
            if (isset($array[$key]))
                $result[$key] = $array[$key];
        }

        return $result;
    }

    public function intersect(array $array1, array $array2)
    {
        $array2 = array_fill_keys($array2, true);
        return array_intersect_key($array1, $array2);
    }
}
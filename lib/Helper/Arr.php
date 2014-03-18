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
}
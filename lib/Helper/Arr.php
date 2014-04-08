<?php

namespace Perfumer\Helper;

class Arr
{
    public function fetch(array $array, array $keys)
    {
        $keys = array_fill_keys($keys, true);
        return array_intersect_key($array, $keys);
    }
}
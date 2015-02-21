<?php

namespace Perfumer\Helper;

class Text
{
    public static function generateString($length = 8)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $string = '';

        for ($i = 0; $i < $length; $i++)
            $string .= $characters[rand(0, 47)];

        return $string;
    }
}
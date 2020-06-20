<?php

namespace Perfumer\Helper;

class Text
{
    /**
     * @param int $length
     * @return string
     */
    public static function generateString($length = 8)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $string = '';

        for ($i = 0; $i < $length; $i++) {
            $string .= $characters[random_int(0, 61)];
        }

        return $string;
    }

    /**
     * @param int $length
     * @return string
     */
    public static function generateAlphabeticString($length = 8)
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $string = '';

        for ($i = 0; $i < $length; $i++) {
            $string .= $characters[random_int(0, 51)];
        }

        return $string;
    }

    /**
     * @param int $length
     * @return string
     */
    public static function generateNumericString($length = 8)
    {
        $characters = '0123456789';

        $string = '';

        for ($i = 0; $i < $length; $i++) {
            $string .= $characters[random_int(0, 9)];
        }

        return $string;
    }
}

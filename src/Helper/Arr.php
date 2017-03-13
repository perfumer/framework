<?php

namespace Perfumer\Helper;

class Arr
{
    /**
     * @param array $array
     * @param mixed $from_value
     * @param mixed $to_value
     * @return array
     */
    public static function convertValues(array $array, $from_value, $to_value)
    {
        foreach ($array as &$value) {
            if (is_array($value)) {
                $value = self::convertValues($value, $from_value, $to_value);
            } else {
                if ($value === $from_value) {
                    $value = $to_value;
                }
            }
        }

        return $array;
    }

    /**
     * @param array $array
     * @param array $keys
     * @return array
     */
    public static function deleteKeys(array $array, array $keys)
    {
        $keys = array_fill_keys($keys, true);

        return array_diff_key($array, $keys);
    }

    /**
     * @param array $array
     */
    public static function dump(array $array)
    {
        echo '<pre>';
        print_r($array);
        echo '</pre>';
    }

    /**
     * @param array $array
     * @param array $keys
     * @param bool|false $add_defaults
     * @param mixed $default_value
     * @return array
     */
    public static function fetch(array $array, array $keys, $add_defaults = false, $default_value = null)
    {
        $keys = array_fill_keys($keys, $default_value);

        if ($add_defaults) {
            $array = array_merge($keys, $array);
        }

        return array_intersect_key($array, $keys);
    }

    /**
     * @param array $array
     * @param string $char
     * @return array
     */
    public static function trim(array $array, $char = ' ')
    {
        foreach ($array as &$value) {
            if (is_array($value)) {
                $value = self::trim($value, $char);
            } elseif (is_string($value)) {
                $value = trim($value, $char);
            }
        }

        return $array;
    }
}

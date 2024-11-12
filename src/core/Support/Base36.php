<?php

namespace Semiorbit\Support;

class Base36
{

    /**
     * Return BASE-36 alphanumeric representative of number [A-Z0-9] in CAPITALS.
     *
     * @param int $int
     * @return string
     */

    public static function Convert(int $int): string
    {
        return strtoupper(base_convert((string) $int, 10, 36));
    }


    /**
     * Convert a bBASE-36 to integer.
     *
     * @param string $base36
     * @return int
     */

    public static function ToInteger(string $base36): int
    {
        return (int) base_convert($base36, 36, 10);
    }


    /**
     * Checks if input is a base36 string
     *
     * @param $input
     * @return bool
     */
    public static function IsBase36($input): bool
    {
        return preg_match('/^[0-9a-z]+$/i', $input) === 1;
    }


}
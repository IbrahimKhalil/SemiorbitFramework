<?php

namespace Semiorbit\Support;

use Semiorbit\Field\Validate;

class FilterInput
{

    protected static int $_InputType = INPUT_GET;


    public static function String($var): ?string
    {
        $val = self::ValueOf($var);

        return $val ? Str::Filter($val) : null;
    }

    public static function Date($var, $format = 'Y-m-d'): ?string
    {
        $val = self::ValueOf($var);

        return $val ? date($format, strtotime($val)) : null;
    }

    public static function DateTime($var, $format = 'Y-m-d H:i:s'): ?string
    {
        $val = urldecode(self::ValueOf($var));

        return $val ? date($format, strtotime($val)) : null;
    }

    public static function Number($var): ?int
    {
        return ($res = filter_input(static::$_InputType, $var, FILTER_SANITIZE_NUMBER_INT))

                    === null || $res === "" || $res === false ? null :  (int) $res;
    }


    public static function NumberFloat($var): ?float
    {
        return ($res = filter_input(static::$_InputType, $var, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION))

        == null || $res === "" || $res === false ? null :  (float) $res;
    }

    
    public static function Boolean($var): bool
    {
        return Validate::IsTrue(filter_input(static::$_InputType, $var));
    }

    public static function Hex($var): ?string
    {
        $val = static::ValueOf($var);

        return ctype_xdigit($val) ? $val : null;
    }





    public static function ValueOf($var)
    {
           return $_GET[$var] ?? null;
    }


}
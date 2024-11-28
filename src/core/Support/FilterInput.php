<?php

namespace Semiorbit\Support;

class FilterInput
{

    protected static int $_InputType = INPUT_GET;


    public static function String($var): ?string
    {
        $val = static::ValueOf($var);

        return $val ? Str::Filter($val) : null;
    }

    public static function Date($var, $format = 'Y-m-d'): ?string
    {
        $val = static::ValueOf($var);

        return Filter::Date($val, $format);
    }

    public static function DateTime($var, $format = 'Y-m-d H:i:s'): ?string
    {
        $val = urldecode(static::ValueOf($var));

        return Filter::DateTime($val, $format);
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

        return Filter::Hex($val);
    }


    public static function Email($var): ?string
    {
        $val = static::ValueOf($var);

        return Filter::Email($val);
    }

    public static function Tel($var, bool $force_leading_plus = false, $default_country_code = null): ?string
    {
        $val = static::ValueOf($var);

        return Filter::Tel($val, $force_leading_plus, $default_country_code);
    }





    public static function ValueOf($var)
    {
           return $_GET[$var] ?? null;
    }


}
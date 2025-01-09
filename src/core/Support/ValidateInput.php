<?php

namespace Semiorbit\Support;

class ValidateInput
{

    protected static int $_InputType = INPUT_GET;


    public static function String($var): ?string
    {
        $val = static::ValueOf($var);

        return $val ? (Str::Filter($val) === $val ? $val : null) : null;
    }

    public static function Date($var, $format = 'Y-m-d'): ?string
    {
        $val = static::ValueOf($var);

        return Filter::Date($val, $format) === $val ? $val : null;
    }

    public static function DateTime($var, $format = 'Y-m-d H:i:s'): ?string
    {
        $val = urldecode(static::ValueOf($var));

        return Filter::DateTime($val, $format) === $val ? $val : null;
    }

    /**
     * Validate if the input is a <b>positive</b> int/bigint id
     *
     * @param $var
     * @param bool $allow_zero
     * @return int|null
     */
    public static function Id($var, bool $allow_zero = false): ?int
    {
        return ($res = filter_input(static::$_InputType, $var, FILTER_VALIDATE_INT))

                    === null || $res === "" || $res === false || ($res < 0 || (!$allow_zero && $res === 0)) ? null :  (int) $res;
    }


    public static function Uuid($var): ?string
    {
        $val = static::ValueOf($var);

        return Validate::IsUuid($val) ? $val : null;
    }

    public static function uniqueId($var): ?string
    {
        $val = static::ValueOf($var);

        return Validate::IsUniqueId($val) ? $val : null;
    }

    public static function Number($var): ?int
    {
        return ($res = filter_input(static::$_InputType, $var, FILTER_VALIDATE_INT))

                    === null || $res === "" || $res === false ? null :  (int) $res;
    }


    public static function NumberFloat($var): ?float
    {
        return ($res = filter_input(static::$_InputType, $var, FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_FRACTION))

        == null || $res === "" || $res === false ? null :  (float) $res;
    }

    
    public static function Boolean($var): bool
    {
        return Validate::IsTrue(filter_input(static::$_InputType, $var));
    }

    public static function Hex($var): ?string
    {
        $val = static::ValueOf($var);

        return Filter::Hex($val) === $val ? $val : null;
    }


    public static function Email($var): ?string
    {
        $val = static::ValueOf($var);

        return Validate::Email($val);
    }

    public static function Tel($var, bool $force_leading_plus = false): ?string
    {
        $val = static::ValueOf($var);

        return Validate::Tel($val, $force_leading_plus);
    }

    public static function Range($var, $min, $max): ?float
    {
        $val = static::ValueOf($var);

        return Validate::Range($val, $min, $max);
    }



    public static function ValueOf($var)
    {
        return $_GET[$var] ?? null;
    }


}
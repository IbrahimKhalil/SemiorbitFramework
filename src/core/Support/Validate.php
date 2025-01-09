<?php
/*
 *----------------------------------------------------------------------------------------------
* FIELD-VIEW - SEMIORBIT FIELD "HTML VIEW" HELPER    	  						    semiorbit.com
*-----------------------------------------------------------------------------------------------
*
*/

namespace Semiorbit\Support;






class Validate
{

	const EMAIL = 'email';

	const TEL = 'tel';
	
	const URL = 'url';

	const INT = 'int';

	const DOUBLE = 'double';

	const DECIMAL = 'decimal';

	const FLOAT = 'float';

	const NUMERIC = 'numeric';

	const REG = 'reg';



    public static function Decimal($value, $to_float = true): mixed
    {
        return filter_var($value, FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_FRACTION) !== false ? ($to_float ? floatval($value) : $value) : null;
    }


    public static function IsDecimal($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_FRACTION) !== false;
    }

    public static function Email($value): ?string
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) ?: null;
    }

    public static function IsEmail($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function Tel($value, $force_leading_plus = false): ?string
    {
        return self::IsTel($value, $force_leading_plus) ? $value : null;
    }

    public static function IsTel($value, $force_leading_plus = false): bool
    {
        return preg_match($force_leading_plus ? '/^\+\d[\d\s\-\(\)]*$/' : '/^\+?\d[\d\s\-\(\)]*$/', $value) === 1;
    }

    public static function URL($value): ?string
    {
        return self::IsURL($value) ? $value : null;
    }

    public static function IsURL($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }



    /**
     * Checks if a value is (true, yes, 1, on, y).
     *
     * @param $value
     * @return bool
     */

    public static function IsTrue($value): bool
    {
        return $value === 'y'  ?: $value === 'Y' ?:

            filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }


    /**
     * <b>Alias for Validate::IsTrue</b><br>
     * Checks if a value is (true, yes, 1, on, y).
     *
     * @param $value
     * @return bool
     */

    public static function IsYes($value): bool
    {
        return Validate::IsTrue($value);
    }


    public static function Range($value, $min, $max): ?float
    {
        return self::InRange($value, $min, $max) ? (float) $value : null;
    }

    public static function InRange($value, $min, $max): bool
    {
        return is_numeric($value) && $value >= $min && $value <= $max;
    }



    public static function IsId($value, bool $allow_zero = false): bool
    {
        return !(($res = filter_var($value, FILTER_VALIDATE_INT))

            === null || $res === "" || $res === false || ($res < 0 || (!$allow_zero && $res === 0)));
    }


    public static function IsUuid(string $uuid): bool
    {

        // Regular expression for a valid UUID (including optional curly braces)

        $pattern = '/^\{?[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[1-5][0-9a-fA-F]{3}\-[89abAB][0-9a-fA-F]{3}\-[0-9a-fA-F]{12}\}?$/';

        // Return the UUID if it matches the pattern; otherwise, return null

        return preg_match($pattern, $uuid) === 1;

    }

    public static function IsUniqueId(string $id): bool
    {
        // Pattern for both standard and high-entropy uniqid

        $pattern = '/^[a-f0-9]{13}(\.[0-9]+)?$/'; // Matches 13 hex characters optionally followed by dot and digits

        return preg_match($pattern, $id) === 1;

    }

}
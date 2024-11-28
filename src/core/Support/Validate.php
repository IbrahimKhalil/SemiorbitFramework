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



    public static function IsDecimal($value): bool
    {
        return Filter::Decimal($value) !== false;
    }

    public static function IsEmail($value): bool
    {
        return Filter::Email($value) !== null;
    }

    public static function IsTel($value, $force_leading_plus = false): bool
    {
        return preg_match($force_leading_plus ? '/^\+\d[\d\s\-\(\)]*$/' : '/^\+?\d[\d\s\-\(\)]*$/', $value) === 1;
    }

    public static function IsURL($value): bool
    {
        return Filter::Url($value) !== null;
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


}
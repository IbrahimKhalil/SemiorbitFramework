<?php
/*
 *----------------------------------------------------------------------------------------------
* FIELD-VIEW - SEMIORBIT FIELD "HTML VIEW" HELPER    	  						    semiorbit.com
*-----------------------------------------------------------------------------------------------
*
*/

namespace Semiorbit\Field;






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


    public static function IsEmail($value)
    {
        return preg_match("/^[_a-z0-9-]+(\\.[_a-z0-9-]+)*@[a-z0-9-]+(\\.[a-z0-9-]+)*(\\.[a-z]{2,10})$/i", $value);
    }

    public static function IsTel($value)
    {
        //TODO:: TEL CHECK
        return $value;
    }

    public static function IsURL($value)
    {
        return filter_var($value, FILTER_VALIDATE_URL);
    }

    public static function IsDecimal($value)
    {
        return is_double($value);
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
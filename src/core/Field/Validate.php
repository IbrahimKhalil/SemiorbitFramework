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
        //TODO:: URL CHECK
        return $value;
    }

    public static function IsDecimal($value)
    {
        return is_double($value);
    }

}
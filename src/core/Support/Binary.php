<?php

namespace Semiorbit\Support;

class Binary
{

    public static function UnHexHelper()
    {

        return function ($value, $is_param) {

            if (! $is_param) $value = "'{$value}'";

            return "UNHEX({$value})";

        };

    }

    public static function HexHelper($field)
    {
        return "HEX({$field}) AS {$field}";
    }


    public static function UnHexTextHelper()
    {

        return function ($value, $is_param) {

            if (! $is_param) $value = "'" . bin2hex($value) . "'";
            
            return "UNHEX({$value})";

        };

    }
    
    public static function Bin2HexStroingHelper()
    {
        
        return function ($value) {
          
            return bin2hex($value);
            
        };
        
    }


}
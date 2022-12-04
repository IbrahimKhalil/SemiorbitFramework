<?php

namespace Semiorbit\Support;

class Binary
{

    public static function UnHexHelper()
    {
        return function ($value) {

            return "UNHEX({$value})";

        };
    }

    public static function HexHelper($field)
    {
        return "HEX({$field}) AS {$field}";
    }


}
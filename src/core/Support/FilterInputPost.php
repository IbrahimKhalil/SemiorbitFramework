<?php

namespace Semiorbit\Support;

class FilterInputPost extends FilterInput
{

    protected static int $_InputType = INPUT_POST;


    public static function ValueOf($var)
    {
        return $_POST[$var] ?? null;
    }

}
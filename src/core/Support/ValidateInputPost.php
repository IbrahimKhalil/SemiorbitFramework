<?php

namespace Semiorbit\Support;

class ValidateInputPost extends ValidateInput
{

    protected static int $_InputType = INPUT_POST;


    public static function ValueOf($var)
    {
        return $_POST[$var] ?? null;
    }

}
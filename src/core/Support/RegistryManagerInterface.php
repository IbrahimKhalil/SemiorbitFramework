<?php


namespace Semiorbit\Support;


interface RegistryManagerInterface
{

    public static function Destroy();

    public static function Store($key, $value);

    public static function Read($key);

    public static function Clear($key);

    /**
     * @param $key
     * @return bool
     */
    public static function Has($key);

}
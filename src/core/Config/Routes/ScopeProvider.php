<?php


namespace Semiorbit\Config\Routes;


final class ScopeProvider
{

    private static $_ActiveScope = Router::DEFAULT_SCOPE;


    public static function ActiveScope()
    {
        return static::$_ActiveScope;
    }

}
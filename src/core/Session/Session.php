<?php
/*
*------------------------------------------------------------------------------------------------
* SEMIORBIT - SESSION MANAGER    					 					 semiorbit.com
*------------------------------------------------------------------------------------------------
*/

namespace Semiorbit\Session;


use Semiorbit\Support\RegistryManagerInterface;

class Session implements RegistryManagerInterface
{

    protected static $_SessionInstance;


    public static function Start($name, $limit = 0, $path = '/', $domain = null, $secure = null)
    {
        static::ActiveSession()->Start($name, $limit, $path, $domain, $secure);
    }

    public static function Store($key, $value)
    {
        static::ActiveSession()->Store($key, $value);
    }

    public static function Read($key)
    {
        return static::ActiveSession()->Read($key);
    }

    public static function Clear($key)
    {
        return static::ActiveSession()->Clear($key);
    }

    public static function Destroy()
    {
        static::ActiveSession()->Destroy();
    }

    public static function Regenerate()
    {
        static::ActiveSession()->Regenerate();
    }

    /**
     * @param $key
     * @return bool
     */
    public static function Has($key)
    {
        return static::ActiveSession()->Has($key);
    }

    /**
     * @return SessionManager
     */

    public static function ActiveSession()
    {
        if ( ! static::$_SessionInstance ) static::$_SessionInstance = new PHPSessionManager();

        return static::$_SessionInstance;
    }


}
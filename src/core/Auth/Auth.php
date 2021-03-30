<?php
/*
*------------------------------------------------------------------------------------------------
* MSG - SEMIORBIT AUTHENTICATION MANAGER   			 				 semiorbit.com
*------------------------------------------------------------------------------------------------
*
*/

namespace Semiorbit\Auth;

use Semiorbit\Base\AppManager;
use Semiorbit\Config\Config;
use Semiorbit\Http\Url;
use Semiorbit\Session\Session;



class Auth
{


    protected static $_AuthInstance;



    public static function ActiveAuth() : AuthInterface
    {
        return static::$_AuthInstance ?: static::UseAuth(new BaseAuth);
    }


    public static function UseAuth(AuthInterface $auth) : AuthInterface
    {
        return static::$_AuthInstance = $auth;
    }




    public static function User() : GenericUser
    {
        return static::ActiveAuth()->User();
    }



    public static function UpdateLoginToken($new_password)
    {
        return static::ActiveAuth()->UpdateLoginToken($new_password);
    }



    public static function UseUser(GenericUser $user = null) : AuthInterface
    {
        return static::ActiveAuth()->UseUser($user);
    }



    public static function Login($identity, $password)
    {
        return static::ActiveAuth()->Login($identity, $password);
    }


    public static function VerifyLoggedUser() : ?GenericUser
    {
        return static::ActiveAuth()->VerifyLoggedUser();
    }


    public static function Logout($destroy_session = true)
    {
        return static::ActiveAuth()->Logout();
    }

    public static function ID()
    {
        return static::ActiveAuth()->ID();
    }




    public static function Allow($roles, $permissions = null)
    {
        static::ActiveAuth()->Allow($roles, $permissions);
    }


    public static function IsSuperAdmin()
    {
        return static::ActiveAuth()->User()->IsSuperAdmin();
    }

    public static function Is($roles)
    {
        return static::ActiveAuth()->User()->Is($roles);
    }

    public static function Can($permissions)
    {
        return static::ActiveAuth()->User()->Can($permissions);
    }

    public static function Check($roles, $permissions = null)
    {
        return $permissions == null ? static::ActiveAuth()->User()->Is($roles) :

            static::ActiveAuth()->User()->Is($roles) && static::ActiveAuth()->User()->Can($permissions);
    }


}

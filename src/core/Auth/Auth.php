<?php
/*
*------------------------------------------------------------------------------------------------
* MSG - SEMIORBIT AUTHENTICATION MANAGER   			 				 semiorbit.com
*------------------------------------------------------------------------------------------------
*
*/

namespace Semiorbit\Auth;

use Semiorbit\Base\AppManager;
use Semiorbit\Config\CFG;
use Semiorbit\Http\Url;
use Semiorbit\Session\Session;



class Auth
{

    protected static $_User;
    
    
    #SESSION VARS
    
    const LOGIN_STR  = 'semiorbit_login_str';
    
    const UID = 'semiorbit_user_id';


    /**
     * @return GenericUser
     */

    public static function User()
    {
        if ( ! static::$_User ) {

            $user = null;

            if ( Session::Has(Auth::UID) && Session::Has(Auth::LOGIN_STR) ) $user = Auth::VerifyLoggedUser();

            static::UseUser($user);

        }

        return static::$_User;
    }
    
    public static function UpdateLoginStr($new_password)
    {
        $user_browser = $_SERVER['HTTP_USER_AGENT'];

        Session::Store(Auth::LOGIN_STR, hash('sha512', $new_password . $user_browser));
    }

    public static function UseUser(GenericUser $user = null)
    {

        if ( ! $user ) {

            /**@var GenericUser $user */

            $user = new CFG::$UsersModel;

            $user->Role()->Value = null;

        }

        static::$_User = $user;

        return new static;

    }

    public static function IsSuperAdmin()
    {
        return static::User()->IsSuperAdmin();
    }

    public static function Login($identity, $password)
    {

        $uid = Auth::User()->Authenticate($identity, $password);

        if ( $uid === false || $uid === null ) {

            Auth::Logout(false);

            Auth::User()->onLoginFailed($identity, $password);

            return false;

        }

        /**@var GenericUser $user */

        $user = new CFG::$UsersModel;

        $user->Read($uid);

        $login_check_point = $user->onBeforeLogin();

        if ($login_check_point === true || $login_check_point === 1) {

            Auth::Logout();

            Auth::UseUser($user);

            $user_browser = $_SERVER['HTTP_USER_AGENT'];

            Session::Start(md5(AppManager::MainApp()->Name()));

            Session::Store(Auth::UID, Auth::User()->ID->Value);

            Session::Store(Auth::LOGIN_STR, hash('sha512', Auth::User()->Password()->Value . $user_browser));

        } else {

            return $login_check_point;

        }

        return Auth::User()->onLogin();

    }

    public static function VerifyLoggedUser()
    {
        /**@var GenericUser $user */

        $user = new CFG::$UsersModel;

        $user->Read( Session::Read(Auth::UID) );

        $user_browser = $_SERVER['HTTP_USER_AGENT'];

        $stored_password = hash('sha512', $user->Password()->Value . $user_browser);

        if ( $stored_password === Session::Read(Auth::LOGIN_STR) ) {

            return $user;

        } else {

            Auth::Logout(false);

            return null;

        }

    }


    public static function Logout($destroy_session = true)
    {

        Auth::User()->onLogout(Auth::ID());

        if ( $destroy_session ) {

            Session::Destroy();

        } else {

            Session::Clear( Auth::LOGIN_STR );

            Session::Clear( Auth::UID );

        }

        static::$_User = null;
    }

    public static function ID()
    {
        return ( Session::Has(Auth::UID) ) ? Session::Read(Auth::UID) : false;
    }


    public static function Is($roles)
    {
        return Auth::User()->Is($roles);
    }

    public static function Can($permissions)
    {
        return Auth::User()->Can($permissions);
    }

    public static function Check($roles, $permissions = null)
    {
        return $permissions == null ? Auth::User()->Is($roles) :

            Auth::User()->Is($roles) && Auth::User()->Can($permissions);
    }

    public static function Allow($roles, $permissions = null)
    {

        if ( ! Auth::Check( $roles, $permissions ) ) {

            Url::setPreviousPage();

            run('Login');

            exit;

        }

    }


}

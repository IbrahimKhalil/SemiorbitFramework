<?php


namespace Semiorbit\Auth;


use Semiorbit\Base\AppManager;
use Semiorbit\Config\Config;
use Semiorbit\Debug\FileLog;
use Semiorbit\Http\Client;
use Semiorbit\Http\Url;
use Semiorbit\Session\Session;

class BaseAuth implements AuthInterface
{


    protected $_User;


    #SESSION VARS

    const KEY_LOGIN_TOKEN  = 'SEMIORBIT_LOGIN_TOKEN';

    const KEY_USER_ID = 'SEMIORBIT_USER_ID';




    public function User() : GenericUser
    {

        return $this->_User ?: $this->UseUser(

            ( Session::Has(static::KEY_USER_ID) && Session::Has(static::KEY_LOGIN_TOKEN) ) ?

                $this->VerifyLoggedUser() : null

        )->_User;

    }



    public function UpdateLoginToken($new_password)
    {
        $user_browser = $_SERVER['HTTP_USER_AGENT'];

        Session::Store(static::KEY_LOGIN_TOKEN, hash('sha512', $new_password . $user_browser));
    }



    public function UseUser(GenericUser $user = null) : AuthInterface
    {

        if ( ! $user ) {

            /**@var GenericUser $user */

            $class_name = Config::UsersModel();

            $user = new $class_name;

            $user->Role()->Value = null;

        }

        $this->_User = $user;

        return $this;

    }



    public function Login($identity, $password)
    {

        $uid = $this->User()->Authenticate($identity, $password);

        if ( $uid === false || $uid === null ) {

            $this->Logout(false);

            $this->User()->onLoginFailed($identity, $password);

            return false;

        }

        /**@var GenericUser $user */

        $class_name = Config::UsersModel();

        $user = new $class_name;

        $user->Read($uid);

        $login_check_point = $user->onBeforeLogin();

        if ($login_check_point === true || $login_check_point === 1) {

            $this->Logout();

            $this->UseUser($user);

            $user_browser = Client::UserAgent();

            Session::Start(md5(AppManager::MainApp()->Name()));

            Session::Store(static::KEY_USER_ID, $this->User()->ID->Value);

            Session::Store(static::KEY_LOGIN_TOKEN, hash('sha512', static::User()->Password()->Value . $user_browser));

        } else {

            return $login_check_point;

        }

        return $this->User()->onLogin();

    }


    public function VerifyLoggedUser() : ?GenericUser
    {

        /**@var GenericUser $user */

        $class_name = Config::UsersModel();

        $user = new $class_name;

        $user->Read( Session::Read(static::KEY_USER_ID) );

        $user_browser = Client::UserAgent();

        $stored_password = hash('sha512', $user->Password()->Value . $user_browser);

        if ( $stored_password === Session::Read(static::KEY_LOGIN_TOKEN) ) {

            return $user;

        } else {

            $this->Logout(false);

            return null;

        }

    }


    public function Logout($destroy_session = true)
    {

        if ($this->_User)

            $this->User()->onLogout($this->ID());

        if ( $destroy_session ) {

            Session::Destroy();

        } else {

            Session::Clear( static::KEY_LOGIN_TOKEN );

            Session::Clear( static::KEY_USER_ID );

        }

        $this->_User = null;

    }

    public function ID()
    {
        return ( Session::Has(static::KEY_USER_ID) ) ? Session::Read(static::KEY_USER_ID) : false;
    }




    public function Allow($roles, $permissions = null)
    {

        if ( ! Auth::Check( $roles, $permissions ) ) {

            Url::setPreviousPage();

            run('login');

            exit;

        }

    }

}
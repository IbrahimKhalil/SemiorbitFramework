<?php
/*
*------------------------------------------------------------------------------------------------
* SEMIORBIT - SESSION MANAGER    					 					 semiorbit.com
*------------------------------------------------------------------------------------------------
*/

namespace Semiorbit\Session;


use Semiorbit\Http\Client;


/**
 * Class PHPSessionManager
 * @package Semiorbit\Session
 *
*/

class PHPSessionManager implements SessionManager
{

    const SES_FINGERPRINT = 'SM_SES_FNP';

    const SES_START_TIME = 'SM_SES_START_TIME';


    protected string $_SessionName;

    protected bool $_SslSecure = false;
    
    private string $_FingerPrint;

    /**
     * This function starts, validates and secures a session.
     *
     * @param string $name The name of the session.
     * @param int $limit Expiration date of the session cookie, 0 for session only
     * @param string $path Used to restrict where the browser sends the cookie
     * @param string $domain Used to allow subdomains access to the cookie
     * @param bool $secure If true the browser only sends the cookie over https
     */

    public function Start($name, $limit = 0, $path = '/', $domain = null, $secure = null)
    {


        ini_set('session.use_trans_sid', 0);

        ini_set('session.use_strict_mode', 1);

        ini_set('session.use_cookies', 1);

        ini_set('session.use_only_cookies', 1);

        ini_set('session.cache_limiter', 'nocache');


        // Set the cookie name

        $this->_SessionName = md5('SEMIORBIT_' . $name . '_SES');

        session_name($this->_SessionName);


        // Set SSL level

        $this->_SslSecure = $secure ?? isset($_SERVER['HTTPS']);


        // Set session cookie options

        session_set_cookie_params($limit, $path, $domain, $this->_SslSecure, true);



        if ( session_id() === '' ) session_start();


        // Check to see if the session is new or a hijacking attempt

        if ( ! $this->Validate() ) {


            // Reset session data and regenerate id

            session_destroy();

            session_start();


            $this->Regenerate();


        }

        // Force change session id after 5 minutes

        elseif (((time() - $_SESSION[self::SES_START_TIME]) > 300)) {

            $this->Regenerate();

        }


    }


    public function Regenerate()
    {
        session_regenerate_id(true);

        $this->StoreSessionFingerprint();
    }


    protected function StoreSessionFingerprint()
    {

        $_SESSION[self::SES_START_TIME] = time();

        $_SESSION['IP'] = Client::IP();

        $_SESSION[static::SES_FINGERPRINT] = $this->FingerPrint();

    }



    public function Validate(): bool
    {

        if (! isset( $_SESSION[static::SES_FINGERPRINT] ) ) return false;

        if( $_SESSION[static::SES_FINGERPRINT] != $this->FingerPrint() ) return false;

        return true;

    }


    protected function FingerPrint(): string
    {
        return $this->_FingerPrint ??
            
            $this->_FingerPrint = md5(getenv('HTTP_USER_AGENT') . $this->_SessionName);
    }


    public function Destroy()
    {
        session_destroy();
    }

    public function Store($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    public function Read($key)
    {
        return $_SESSION[$key];
    }

    public function Clear($key)
    {
        if ( isset( $_SESSION[$key] ) ) unset( $_SESSION[$key] );
    }

    public function Has($key)
    {
        return isset( $_SESSION[$key] );
    }

}
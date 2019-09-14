<?php
/*
*------------------------------------------------------------------------------------------------
* SEMIORBIT - SESSION MANAGER INTERFACE			 					 semiorbit.com
*------------------------------------------------------------------------------------------------
*/

namespace Semiorbit\Session;



interface SessionManager
{

    /**
     * This function starts, validates and secures a session.
     *
     * @param string $name The name of the session.
     * @param int $limit Expiration date of the session cookie, 0 for session only
     * @param string $path Used to restrict where the browser sends the cookie
     * @param string $domain Used to allow subdomains access to the cookie
     * @param bool $secure If true the browser only sends the cookie over https
     */

    public function Start($name, $limit = 0, $path = '/', $domain = null, $secure = null);


    public function Regenerate();

    public function Destroy();

    public function Store($key, $value);

    public function Read($key);

    public function Clear($key);

    /**
     * @param $key
     * @return bool
     */
    public function Has($key);

    public function Validate();

}
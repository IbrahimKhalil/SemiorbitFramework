<?php
/* 
 *------------------------------------------------------------------------------------------------
 * SEMIORBIT		   								 					 semiorbit.com
 *------------------------------------------------------------------------------------------------
 */

namespace Semiorbit\Controllers;

 
class LoginController extends \Semiorbit\Auth\Login
{

    function Index()
    {
        return $this->View;
    }

} 
<?php
/* 
 *------------------------------------------------------------------------------------------------
 * SEMIORBIT		   								 					 semiorbit.com
 *------------------------------------------------------------------------------------------------
 */


namespace Semiorbit\Auth;



use Semiorbit\Http\Controller;
use Semiorbit\Http\Url;


class Logout extends Controller
{

	protected function onStart()
	{

        $this->Actions->ExplicitMode()->Define(array(

            'index'         => array( 	'method'=>'Index' )

        ));
        
	}


	public function Index()
	{

		Auth::Logout();

		Url::GotoHomePage();

	}
	
}
<?php
/* 
 *------------------------------------------------------------------------------------------------
 * SEMIORBIT		   								 					 semiorbit.com
 *------------------------------------------------------------------------------------------------
 */

namespace Semiorbit\Controllers;


/**
 * URL Controller
 * 
 * If user requested http://some-domain.com/en/some-class/
 * and some-class was not found in default application controllers path and Default Controller has nothing to do with that, 
 * this will be used to redirect url or GOTO(404).   
 *
 * @author		semiorbit.com
 */
 
class URLControllerController extends \Semiorbit\Http\Controller
{

	public function Index()
	{
		// LOOK FOR REDIRECTION PATH
	
		//NOTHING TO DO? >> GOTO 404
		//abort(404, true);
		run('Main');
	}

} 
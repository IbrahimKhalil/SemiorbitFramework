<?php
/*
 *-----------------------------------------------------------------------------------------------
* SEMIORBIT - DEFAULT CONTROLLER					 					 semiorbit.com
*------------------------------------------------------------------------------------------------
*/

namespace Semiorbit\Controllers;
use Semiorbit\Base\Application;
use Semiorbit\Config\Config;


/**
 * Default Controller
 * 
 * If user requested http://some-domain.com/en/some-class/
 * and some-class was not found in default application controllers path, 
 * this will be used.   
 *
 * @author		semiorbit.com
 */
 
class DefaultController extends \Semiorbit\Http\Controller
{

	public function Index()
	{

	    if (Config::ApiMode())

	        Application::Abort(404);

	    else

	        run(Config::MainPage());

	}

} 
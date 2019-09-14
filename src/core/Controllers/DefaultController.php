<?php
/*
 *-----------------------------------------------------------------------------------------------
* SEMIORBIT - DEFAULT CONTROLLER					 					 semiorbit.com
*------------------------------------------------------------------------------------------------
*/

namespace Semiorbit\Controllers;
use Semiorbit\Config\CFG;


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
		//NOTHING TO DO? >> GOTO URLController
		run( CFG::$URLController . '/' . $this->Request->PathInfo);
		
	}

} 
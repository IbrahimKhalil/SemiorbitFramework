<?php

namespace Semiorbit\Controllers;

 
use Semiorbit\Auth\Auth;
use Semiorbit\Base\Application;
use Semiorbit\Support\Path;

class RunController extends \Semiorbit\Http\Controller
{
	
	public function onStart()
	{
		
		$this->Actions->ExplicitMode()->Define(array(
			
			'index' => array( 	'method'=>'Index',	  'pms' => 'folder/file', 		'allow'=>SUPER_ADMIN )
	
		));
		
	}
	
	public function Index()
	{

		$this->Request->PathInfoPattern = "/folder/file";
		
		$params = $this->Request->ParsePath();
		
		if ( isset( $params['file'] ) && $params['file'] != "" ) {
			
			if ( ! in_array( $params['folder'], array('ext', 'css', 'js'))) die('Access Denied');
			
			if ( ! in_array( $params['folder'], array('css', 'js'))) /***/Auth::Allow(SUPER_ADMIN);/***/;
		
			
		} else {
			
			$params['file'] = $params['folder'];
			
			$params['folder'] = Path::Normalize(realpath(FW . "../../ext"));
			
		}
		
		$path = $params['folder'] . $params['file']; //Finder::LookFor($params['file'],$params['folder'],true);

		if ( empty($path) || ! file_exists($path) ) Application::Abort(404);

        /** @noinspection PhpIncludeInspection */
        include_once $path;
	}
	

}
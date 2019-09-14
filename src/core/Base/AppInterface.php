<?php 
/*
*------------------------------------------------------------------------------------------------
* SEMIORBIT - APP INTERFACE   					 					 semiorbit.com
*------------------------------------------------------------------------------------------------
*/

namespace Semiorbit\Base;



use Semiorbit\Http\Request;

interface AppInterface
{

    public static function onCreate();

	public static function onStart();

    public static function onLoad(Request $request);

    public static function onRun(&$output);

    public static function onStop();

    public static function onFinish();

    public static function Title();
	
}

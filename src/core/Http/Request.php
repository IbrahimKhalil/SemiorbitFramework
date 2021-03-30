<?php
/*
 *-----------------------------------------------------------------------------------------------
* SEMIORBIT - Uri/HMVC REQUEST PARSER	   			 					 semiorbit.com
*------------------------------------------------------------------------------------------------
*/
  
namespace Semiorbit\Http;



use Semiorbit\Base\Application;
use Semiorbit\Config\Config;
use Semiorbit\Component\Finder;
use Semiorbit\Routes\Router;
use Semiorbit\Support\Str;
use Semiorbit\Translation\Lang;


/**
 * Class Request
 * Parses Uris, Set Routing, Execute HMVC Requests, Check Uris for Security
 *
 * @package Semiorbit\Http
 * @property \Semiorbit\Http\Controller $Class
 * @property \Semiorbit\Component\FinderResult $Controller
 * @property Action $Action
 */

class Request {

    #region "VERBS"

    const VERB_GET = 'GET';

    const VERB_POST = 'POST';

    const VERB_DELETE = 'DELETE';

    const VERB_PUT = 'PUT';

    const VERB_PATCH = 'PATCH';

    #endregion

	public $Uri = "";
	
	public $Params = array();
	
	public $Lang = "";
	
	public $Controller;
	
	public $Class;
	
	public $Action;
	
	public $ID = "";
	
	public $PathInfo = "";
	
	public $QueryString = "";
	
	public $ServerVars = array();
	
	public $PathInfoPattern = "/id";
	
	public $ReadServerVars = true;

	public $Verb;

	public $Callable;

    protected $_Path;

	protected static $_Url;
	
	protected static $_StartupRequest;
	
	protected static $_LastRunRequest;



	
	function __construct($Uri = '', $extra_path_info_pattern = '/id')
	{

        $this->Verb = $_SERVER['REQUEST_METHOD'];

        if ( ! is_empty( $Uri ) ) {

			$this->Load($Uri, $extra_path_info_pattern);

		}

	}
	
	public function Load($Uri = '', $extra_path_info_pattern = '/id')
	{

	    //$time = microtime(true);

	    $this->PathInfoPattern = $extra_path_info_pattern;
		
		if ( is_empty( $Uri ) )
		{ 
			//AUTO DETECT Uri FROM $_SERVER
			
			$this->DetectUri();
				
		} else {
			
			//HMVC REQUEST

			$parse_path_query = Url::Utf8ParseUrl($Uri);
			
			$this->PathInfo = isset( $parse_path_query['path'] ) ? $parse_path_query['path'] : "";
			
			$this->QueryString = isset( $parse_path_query ['query'] ) ? $parse_path_query ['query'] : "";

			//TODO: MAKE SURE THIS IS WORKING PROPERLY IN ALL CASES

            // THIS WILL LOAD PARAMS FROM QUERY STRING ONLY.
            // [$_REQUEST] ARRAY WILL BE IGNORED AS LONG AS THIS IS AN HMVC REQUEST

			$this->ReadServerVars = false;
			
		}
		

		// READ QUERY
		if ($this->ReadServerVars) {
		
			//Merge QUERY-STRING and REQUEST ARRAY
		
			// First Select REQUEST Vars into $_REQUEST
			## NB. These vars will be overridden by PATH_INFO and  QUERY_STRING Later
			
			$this->ServerVars = array_merge($this->ServerVars, $_REQUEST);
		
		} else {
		
			$query_arr = $this->ParseQuery($this->QueryString);
			
			$this->ServerVars = array_merge($this->ServerVars, $query_arr);
			
		}


        //Add PARAMS from QUERY_STRING

        $this->Params = $this->ServerVars;


        // DETECT LANG
		$this->Lang = $this->DetectLang();

		Lang::UseLang( $this->Lang );


		// DETECT ROUTES

        // TODO: DETECT API CALL

        Router::LoadRoutes();

        [$controller, $action, $this->PathInfo, $route_params, $callable] = Router::Find($this->PathInfo, $this->Verb);




        if ($callable) {

            $this->Callable = $callable;

        } else {

            // DETECT CONTROLLER

            $this->Controller = $controller ?:

                (Config::ApiMode() ? $this->DetectApiController() : $this->DetectController());

            $this->LoadController();

            // DETECT ACTION

            $this->Action = $this->DetectAction($action);

        }

		// PARSE PATH


		//Add PARAMS from PATH_INFO
		
		## NB. This will override the server pms when they have same keys.
		
		$path_arr = $route_params ?: $this->ParsePath();
		
		$this->Params = array_merge($this->Params, $path_arr);
	

		$this->ID = isset( $this->Params[ Config::IDParamName() ] ) ? $this->Params[ Config::IDParamName() ] : "";

		//dd(microtime(true) - $time);

	}
	
	
	private function DetectLang()
	{
		// LANG will be read in order from : PATH_INFO > QueryString > $_REQUEST > CONFIG

		$lang = Lang::ActiveLang() ? Lang::ActiveLang() :  Config::DefaultLang();
		
		// GET LANG FROM - IN ORDER - : QueryString then $_REQUEST  
		
		if ( isset( $this->ServerVars[ Config::LangParamName() ] ) && in_array($this->ServerVars[ Config::LangParamName() ], Config::Languages())) {
			
			$lang = strtolower( $this->ServerVars[ Config::LangParamName() ] );
			
			unset($this->ServerVars[ Config::LangParamName() ]);
			
		}
		
		// TRY TO EXTRACT "LANG" FROM PATH_INFO

		$pms = static::Path2Array($this->PathInfo);
	
		// NOT SET GO TO DEFAULT LANG
		
		// SET :: VERIFY [AGAINST LANG ARRAY]!

        if ( isset($pms[0]) )
		
		if ( in_array( strtolower($pms[0]), Config::Languages())) {

			
			// FOUND ! THANKS :)
			
			$lang = strtolower( $pms[0] );
			
			// RESET PATH_INFO EXCLUDING LANG
			
			unset($pms[0]);
			
			$this->PathInfo = implode("/", $pms);
		
		}
	
		
		return $lang;
		
	}
	
	public function DetectController() 
	{

        $controller = null;

        $api_call = false;


        // TRY TO EXTRACT "CONTROLLER" FROM PATH_INFO

        $path_info_controller = '';

        $pms = static::Path2Array( $this->PathInfo );


        if ( isset( $pms[0] ) ) {

            $segment_0 = Str::PascalCaseByHyphen( Str::Sanitize($pms[0]) );


            // Check if request is an API call

            if ( $segment_0 == Config::ApiControllersDir() ) {

                if ( isset($pms[1]) && isset($pms[2])) {

                    $segment_1 = Str::PascalCaseByHyphen( Str::Sanitize($pms[1]) );

                    $segment_2 = Str::PascalCaseByHyphen( Str::Sanitize($pms[2]) );

                    // Api controller namespace

                    $path_info_controller = "{$segment_0}\\{$segment_1}\\{$segment_2}";

                    $api_call = true;

                }

            } else {

                $path_info_controller = $segment_0;

            }

        }
		


        if ( ! is_empty($path_info_controller) ) {

            // FIND CONTROLLER BY NAME

            $controller = Finder::LookForController( array($path_info_controller) );


            //==========================================================================================================

        }

        if ( $controller && $controller->Selector == $path_info_controller ) {

            // RESET PATH_INFO EXCLUDING CONTROLLER

            unset($pms[0]);

            if ($api_call) { unset($pms[1]); unset($pms[2]); }

            $this->PathInfo = implode("/", $pms);

        }

        return $controller ?: Config::MainPage();
		
	}

	public function DetectApiController()
	{

        $controller = null;


        // TRY TO EXTRACT "CONTROLLER" FROM PATH_INFO

        $path_info_controller = '';

        $pms = static::Path2Array( $this->PathInfo );


        if ( isset( $pms[0] ) ) {

            $segment_0 = Str::PascalCaseByHyphen( Str::Sanitize($pms[0]) );

            if ( $segment_0 )

                    $path_info_controller = $segment_0;
        }



        if ( ! is_empty($path_info_controller) ) {

            // FIND CONTROLLER BY NAME

            $controller = Finder::LookForController( $path_info_controller, true );

        }


        if ( $controller && $controller->Selector == $path_info_controller ) {

            // RESET PATH_INFO EXCLUDING CONTROLLER

            unset($pms[0]);

            $this->PathInfo = implode("/", $pms);

        }


		return $controller ?: Config::HttpError();

	}

	public function LoadController()
	{
		
		$class_name = $this->Controller->Class;

        if ( ! class_exists($class_name) ) Application::Abort(404, "Controller ({$class_name}) was not found!");

		$this->Class = new $class_name($this);

        if ( ! $this->Class instanceof Controller ) Application::Abort(404, "Object ({$class_name}) is not a controller!");
		
	}
	
	public function DetectAction($action = null)
	{

	    if ($action) return $this->Class->Actions->Action($action);

	    // TODO: ACTIONS CACHE!

        // Action will be read in order from : PATH_INFO > QueryString > $_REQUEST > CONFIG
		
		$action = null;

		
		// TRY TO EXTRACT "ACTION" FROM PATH_INFO
		
		$pms = static::Path2Array($this->PathInfo);
		
		$actions = $this->Class->Actions->All();


		// NOT SET GO TO Index Action
		
		if ( isset($pms[0]) && $pms[0] != '' )  {

			$pms[0] = Actions::NormalizeAlias( Str::Sanitize($pms[0]) );
			
			if ( isset( $actions[ $pms[0] ] ) ) {


                $action = $actions[$pms[0]];


                /** @var $action Action */

                if ($action->IsVerbAccepted($this->Verb)) {


                    // RESET PATH_INFO EXCLUDING LANG

                    unset($pms[0]);

                    $this->PathInfo = implode("/", $pms);

                }

            }
			
			 
		}


        if (!$action) {


		    if ($action_alias = $this->Class->Actions->DefaultByVerb($this->Verb, count($pms)))

		        $action = $actions[$action_alias] ?? null;


		    if (!$action) $action = $this->Class->Actions->Index();

		    if (!$action->IsVerbAccepted($this->Verb)) $action = null;

        }
		

		if ($action) $this->PathInfoPattern = $action->Pms;

		else Application::Abort(404, "Action not found for verb {$this->Verb}!");


		return $action;
		
		
	}
	
	public static function Path2Array($path)
	{
		//CONVERT PATH TO ARRAY
		
		$pms = $path ? explode("/", trim ($path, "/")) : [];

		return $pms;
	}
	
	public function Query2Array($query) 
	{
		$arr = array();
		
		parse_str($query, $arr);
		
		return $arr;
	}
	
	public function ParsePath() 
	{
		return $this->Path()->ToArray();
	}

    /**
     * @param string $path_pattern
     * @return PathInfo
     */

    public function Path($path_pattern = null)
    {

        if ( ! $this->_Path ) $this->_Path = new PathInfo( $this->PathInfo, $this->PathInfoPattern );

        if ($path_pattern) $this->PathInfoPattern = $path_pattern;


        if ( $this->_Path->Path() != $this->PathInfo || $this->_Path->Pattern() != $this->PathInfoPattern )

            $this->_Path->UsePath( $this->PathInfo, $this->PathInfoPattern );

        return $this->_Path;

    }
	
	public function ParseQuery($QueryString) 
    {

		$arr = $this->Query2Array($QueryString);
		
		return $arr;
		
	}
	
	
	public function setParam($param, $value, $BasicParamCustomNaming = array('lang'=>"lang",'class'=>"class",'action'=>"action",'id'=>"id"))
	{
		$this->Params[$param] = $value;
		
		$this->setBasicParams( $BasicParamCustomNaming );
	}
	
	public function Uri()
	{
		return $this->Uri;
	}
	
	public function DetectUri()
	{

        // path_info_pattern = "lang/class/action/id"

		if ( ! isset($_SERVER['REQUEST_URI'])) return $this;
			
		$this->Uri = $_SERVER['REQUEST_URI'];

		$this->PathInfo = Request::PathInfo();


		// Get QUERY_STRING - Checking two ways for retrieving QUERY_STRING.
		
		$query_sting =  (isset($_SERVER['QUERY_STRING'])) ? $_SERVER['QUERY_STRING'] : @getenv('QUERY_STRING');
		
		$this->QueryString = $query_sting;


        return $this;
		
	}
	
	public function setBasicParams( $BasicParamCustomNaming = array('lang'=>"lang",'class'=>"class",'action'=>"action",'id'=>"id") )
	{
		
		if ( is_array($this->Params) ) 
		{
			$this->Lang = isset( $this->Params[$BasicParamCustomNaming ['lang']] ) ?  $this->Params[$BasicParamCustomNaming ['lang']] : null;
			
			$this->Class = isset( $this->Params[$BasicParamCustomNaming ['class']] ) ? $this->Params[$BasicParamCustomNaming ['class']] : null;
			
			$this->Action = isset( $this->Params[$BasicParamCustomNaming ['action']] ) ? $this->Params[$BasicParamCustomNaming ['action']] : null;
			
			$this->ID = isset( $this->Params[$BasicParamCustomNaming ['id']] ) ? $this->Params[$BasicParamCustomNaming ['id']] : "";
		}
		
	}
	
	public function CleanParams($params)
	{
		
		if ( ! is_array($params)) return false;
		
		return $params;	
		
	}

	public static function PathInfo()
    {

        // Get PATH_INFO - Checking Two Ways for Retrieving PATH_INFO.

        $path_info = (isset($_SERVER['PATH_INFO'])) ? $_SERVER['PATH_INFO'] : @getenv('PATH_INFO');

        if ( empty( $path_info ) ) $path_info = isset( $_SERVER['ORIG_PATH_INFO'] ) ? $_SERVER['ORIG_PATH_INFO'] : '';

        if ( empty( $path_info ) ) $path_info = SymfonyRequest::Load()->getPathInfo();

        return $path_info;

    }
	
	public static function Url()
	{
	
		if ( ! is_empty( self::$_Url ) ) return self::$_Url; 

		$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';
	
		return self::$_Url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];	
	
	}
	
	public static function Startup()
	{
	
		if ( empty( self::$_StartupRequest ) )
	
			self::$_StartupRequest = new Request();
	
		return self::$_StartupRequest;
	
	}


    public static function StartupControllerName()
    {
        return Controller::Name( Request::Startup()->Controller['class'] );
    }


    /**
     * @return string Action alias
     */

	public static function StartupAction()
	{
		return Request::Startup()->Action->Alias;
	}
	
	
	public static function LastRun(Request $request = null)
	{
	
		if ( ! empty( $request ) ) self::$_LastRunRequest = $request;
	
		return $request = self::$_LastRunRequest;
	
	}



}

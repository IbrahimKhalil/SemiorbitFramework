<?php  
/*
 *-----------------------------------------------------------------------------------------------
* SEMIORBIT -	CONFIGURATION MANAGEMENT CLASS		 					 semiorbit.com
*------------------------------------------------------------------------------------------------
*/

namespace Semiorbit\Config;


use Semiorbit\Component\Finder;
use Semiorbit\Http\Actions;
use Semiorbit\Support\Path;


class CFG extends DefaultConfig
{

    const ENV_DEVELOPMENT = 'dev';

    const ENV_PRODUCTION = 'production';

	
	public static $DefConfig = array();

	public static $FrameworkConfig = array();


	public static function LoadConfigFromClass($config_class='Config') 
	{

	    // Framework Config

        self::$FrameworkConfig = include __DIR__ . '/framework.inc';
		
		// DEFAULT CONFIG 
		$def_config = new \ReflectionClass('Semiorbit\\Config\\DefaultConfig');
		$arrA = $def_config->getStaticProperties();
		
	    //LOAD APP'S CONFIG FROM CONFIG CLASS
		foreach ($arrA as $k=>$v) {
			self::$DefConfig[$k] = $v;
		}
		
		//CONFIG::
		$class = new \ReflectionClass($config_class);
		
		//SELF::
		$self_class = new \ReflectionClass('Semiorbit\\Config\\CFG');
		$arr = $class->getStaticProperties();
		
		//LOAD APP'S CONFIG FROM CONFIG CLASS 
		foreach ($arr as $k=>$v) {
			$self_class->setStaticPropertyValue($k,$v);
		}

	}
	
	public static function DefaultLang()
	{
		//DEPRECATED//////////////
	
		if (isset( self::$Lang['default'] )) self::$DefaultLang = self::$Lang['default'];
		
		//////////////////////////
		
		if ( is_empty ( self::$Lang ) ) {
			
			if ( ! is_array( self::$Lang )) self::$Lang = array();
			
			if ( is_string( self::$DefaultLang ) && ! is_empty( self::$DefaultLang )) {
				
				self::$Lang = array( self::$DefaultLang );
				
			} else {
				
				self::$Lang = array('en');
				
				self::$DefaultLang = 'en';
	
			}
			
			return self::$DefaultLang;
				
		}
		
	
		if ( ! in_array( self::$DefaultLang, self::$Lang ) ){
	
			self::$DefaultLang = array_shift(array_slice(self::$Lang, 0, 1));
			
		}
		
		return self::$DefaultLang;
		
	}
	
	
	public static function LangParamName() 
	{
		if (isset( CFG::$BasicParamCustomNaming['lang'] ))	
			return CFG::$BasicParamCustomNaming['lang'];
		else return 'lang';
	}
	
	public static function ControllerParamName() 
	{
		if (isset( CFG::$BasicParamCustomNaming['controller'] ))	
			return CFG::$BasicParamCustomNaming['controller'];
		else return 'controller';
	}
	
	public static function ActionParamName() 
	{
		if (isset( CFG::$BasicParamCustomNaming['action'] ))	
			return CFG::$BasicParamCustomNaming['action'];
		else return 'action';
	}
	
	public static function IDParamName() 
	{
		if (isset( CFG::$BasicParamCustomNaming['id'] ))	
			return CFG::$BasicParamCustomNaming['id'];
		else return 'id';
	}
	
	public static function LayoutParamName()
	{
		if (isset( CFG::$BasicParamCustomNaming['layout'] ))
			return CFG::$BasicParamCustomNaming['layout'];
		else return 'layout';
	}
	
	public static function MainPage() 
	{

        $myFallbackController =

            isset(self::$MainPage) ?

                Finder::LookForController(self::$MainPage) : false;

        if (!$myFallbackController) {

            Finder::LookForController(['Index', 'Home', 'Main']);

            if ($myFallbackController) CFG::$MainPage = $myFallbackController->Class;

        }

        return $myFallbackController;


	}

    /**
     * Api fallback controller
     *
     * @return bool|\Semiorbit\Component\FinderResult
     */

	public static function HttpError()
	{

        $myFallbackController =

            isset(self::$HttpErrorController) ?

                Finder::LookForController(self::$HttpErrorController) : false;

        if (!$myFallbackController) {

            Finder::LookForController('HttpError');

            if ($myFallbackController) CFG::$HttpErrorController = $myFallbackController->Class;

        }

        return $myFallbackController;

	}


	
	public static function Actions() 
	{
		return Actions::PrepareActions(self::$Actions);
	}
	
	public static function Connections($con = null)
	{
		
		static $clean_cons = array();
		
		if ( self::$Connections != $clean_cons ) :

            $default_con = array( 'id' => null, 'host' => 'localhost', 'user' => 'root', 'password' => '', 'db' => '', 'port' => null, 'socket' => null, 'persistent' => true );
		
		foreach ( self::$Connections as $k => $v ){

            self::$Connections[$k] = array_merge( $default_con, self::$Connections[$k] );
			
			self::$Connections[$k]['id'] = $k;	
			
			if ( ! isset( self::$Connections[$k]['persistent'] ) ) self::$Connections[$k]['persistent'] = true;
			
			if ( self::$Connections[$k]['driver'] != 'mysqli' ) { 
			
				$driver = strtolower ( self::$Connections[$k]['driver'] );
				
				if ( $driver == 'mysql' || $driver == 'mysqli' ) {
					
					unset( self::$Connections[$k] );
					
					self::$Connections['mysqli'] = $v;
					
					self::$Connections['mysqli']['driver'] = 'mysqli';
					
				}
				
				elseif ( $driver == 'pdo' ) {
					
					self::$Connections[$k]['driver'] = 'pdo:mysql';
					
					self::$Connections[$k]['pdo_driver'] = 'mysql';
							 
				} elseif ( stristr($driver, 'pdo:') )  {
				
					$driver = explode(':', self::$Connections[$k]['driver']);
			
					if ( array_key_exists(1, $driver) && strtolower( $driver[0] ) == 'pdo'  ) {
						
						self::$Connections[$k][ 'pdo_driver' ] = $driver[1];
						
					}
					
				}
			
			}
		
		}
		
		$clean_cons = self::$Connections;
		
		endif;
		
		reset( self::$Connections );

		if ( is_empty( $con ) )	return current( self::$Connections );
		
		$connection = isset( self::$Connections[ $con ] ) ? self::$Connections[ $con ] : false;  
		
		return $connection;
	
	}
	
	
	public static function FormTemplate()
	{
		
		if ( self::$DefaultFormTemplate == null || trim( self::$DefaultFormTemplate == "" ) ) return "form";
		
		else return self::$DefaultFormTemplate;
		
	}

    public static function DocumentsPath($real_path = true)
    {

        $public_path = $real_path ? PUBLICPATH : '';

        if ( ! is_empty( CFG::$DocumentsPath ) ) $documents_path = Path::Normalize(CFG::$DocumentsPath);

        else if ( ! Path::IsAbsolute( CFG::$DocumentsPath ) ) $documents_path = $public_path . Path::Normalize( CFG::$DocumentsPath );

        else $documents_path = $public_path . 'documents';

        return $documents_path;


    }

	public static function DocumentsURL()
	{

        if ( ! is_empty( CFG::$DocumentsURL ) ) $documents_url = Path::Normalize(CFG::$DocumentsURL);

        else if ( ! Path::IsAbsolute( CFG::$DocumentsPath ) ) $documents_url = PUBLIC_URL . Path::Normalize( CFG::$DocumentsPath );

        else $documents_url = PUBLIC_URL . 'documents';

        return $documents_url;

	}
	
}

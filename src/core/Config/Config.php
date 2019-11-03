<?php  
/*
 *-----------------------------------------------------------------------------------------------
* SEMIORBIT -	CONFIGURATION MANAGEMENT CLASS		 					 semiorbit.com
*------------------------------------------------------------------------------------------------
*/

namespace Semiorbit\Config;


use Semiorbit\Base\Application;
use Semiorbit\Cache\FrameworkCache;
use Semiorbit\Component\Finder;
use Semiorbit\Support\Path;


final class Config extends DefaultConfig
{


    private static $__DefConfig = [];

	private static $__Config = [];


	private static $__Loaded;


	final public static function Load($reload = false)
	{

	    if (static::$__Loaded && !$reload) return;


	    if (! self::$__Config = FrameworkCache::ReadVar('config')) {


            self::$__Config = self::LoadGroups(Application::ConfigPath());

            self::$__Config[self::GROUP__FRAMEWORK] = self::LoadDefaultGroup(self::GROUP__FRAMEWORK);


            FrameworkCache::StoreVar('config', self::$__Config);

        }

    }


	final public static function LoadDefaultGroup($config_group)
    {
        /** @noinspection PhpIncludeInspection */
        return include FW . "core/Config/{$config_group}.inc";
    }
    
    
    final public static function LoadGroup($config_group)
    {
        /** @noinspection PhpIncludeInspection */
        return include Application::ConfigPath() . "{$config_group}.inc";
    }

    final protected static function LoadGroups($path)
    {

        $groups = [];

        if ($handle = opendir($path)) {

            while (false !== ($file = readdir($handle)))

                if (ends_with($file, '.inc'))

                    $groups[substr($file, 0, strlen($file) - 4)] = include $path . "/{$file}";

            closedir($handle);

        }

        return $groups;

    }


    /**
     * @param $config_key
     * @param null $fallback
     * @return mixed
     */
    public static function Of($config_key, $fallback = null)
    {
        [$group, $key] = explode(".", $config_key);

        return static::ValueOf($group, $key, $fallback);
    }

    /**
     * @param $config_group
     * @param $config_key
     * @param null $fallback
     * @return mixed
     */
    final public static function ValueOf($config_group, $config_key, $fallback = null)
    {
        return self::$__Config[$config_group][$config_key] ??

            ($fallback ?: self::MissingConfig($config_group, $config_key));
    }


    final public static function MissingConfig($config_group, $config_key)
    {
        FrameworkCache::Clear('config');

        Application::Abort(503, "Missing config: ({$config_group}.{$config_key})");
    }

    final public static function setValueOf($config_group, $config_key, $value)
    {
        self::$__Config[$config_group][$config_key] = $value;
    }

    final public static function DefaultValueOf($config_group, $config_key, $fallback = null)
    {
        if (! isset(static::$__DefConfig[$config_group])) static::LoadDefaultGroup($config_group);

        return self::$__DefConfig[$config_group][$config_key] ?? $fallback;
    }

    /**
     * @return array
     */
    final public static function FrameworkConfig()
    {
        return self::$__Config[self::GROUP__FRAMEWORK];
    }


    final public static function List()
    {
        return static::$__Config;
    }

    final public static function ListDefault()
    {
        return static::$__DefConfig;
    }

    #region "DEFAULTS---------------------------------------------------"

    public static function StructureDirectory($group, $fallback = null)
    {
        return trim(static::ValueOf($group, "dir", $fallback), '/');
    }

    public static function StructureExtension($group, $fallback = null)
    {
        return static::ValueOf($group, "ext", $fallback);
    }

    public static function ActivateSanitizeOutput($activate = true)
    {
        self::setValueOf(self::GROUP__APP, self::APP__SANITIZE_OUTPUT, $activate);
    }

	public static function DefaultLang()
	{
		return self::Languages()['default'] ?? self::Languages()[0];
	}
	
	
	public static function LangParamName() 
	{
        return Config::BasicParamCustomNaming('lang') ?: 'lang';
    }

	
	public static function IDParamName() 
	{
        return Config::BasicParamCustomNaming('id') ?: 'id';
    }
	
	public static function LayoutParamName()
	{
        return Config::BasicParamCustomNaming('layout') ?: 'layout';
    }


	public static function MainPage() 
	{

        static $myFallbackController;

        if ($myFallbackController) return $myFallbackController;


        $config_main_page = static::ValueOf(self::GROUP__APP, self::APP__MAIN_PAGE);


        $myFallbackController =

            !empty($config_main_page) ?

                Finder::LookForController($config_main_page) : false;


        if (!$myFallbackController) {

            $myFallbackController =

                Finder::LookForController(['Index', 'Home', 'Main']);

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

	    static $myFallbackController;

	    if ($myFallbackController) return $myFallbackController;


	    $config_http_error_controller = static::ValueOf(self::GROUP__API, self::API__HTTP_ERROR_CONTROLLER);

	    $myFallbackController =

            !empty($config_http_error_controller) ?

                Finder::LookForController($config_http_error_controller) : false;

        if (!$myFallbackController) {

            $myFallbackController =

                Finder::LookForController('HttpError');

        }

        return $myFallbackController;

	}


	

	
	public static function Connections($con = null)
	{
		
		static $clean_cons = array();

		$connections = static::DbConnections();

		if ( $connections != $clean_cons ) :

            $default_con = array( 'id' => null, 'host' => 'localhost', 'user' => 'root', 'password' => '', 'db' => '', 'port' => null, 'socket' => null, 'persistent' => true );
		
		foreach ( $connections as $k => $v ){

            $connections[$k] = array_merge( $default_con, $connections[$k] );
			
			$connections[$k]['id'] = $k;	
			
			if ( ! isset( $connections[$k]['persistent'] ) ) $connections[$k]['persistent'] = true;
			
			if ( $connections[$k]['driver'] != 'mysqli' ) { 
			
				$driver = strtolower ( $connections[$k]['driver'] );
				
				if ( $driver == 'mysql' || $driver == 'mysqli' ) {
					
					unset( $connections[$k] );
					
					$connections['mysqli'] = $v;
					
					$connections['mysqli']['driver'] = 'mysqli';
					
				}
				
				elseif ( $driver == 'pdo' ) {
					
					$connections[$k]['driver'] = 'pdo:mysql';
					
					$connections[$k]['pdo_driver'] = 'mysql';
							 
				} elseif ( stristr($driver, 'pdo:') )  {
				
					$driver = explode(':', $connections[$k]['driver']);
			
					if ( array_key_exists(1, $driver) && strtolower( $driver[0] ) == 'pdo'  ) {
						
						$connections[$k][ 'pdo_driver' ] = $driver[1];
						
					}
					
				}
			
			}
		
		}
		
		$clean_cons = $connections;
		
		endif;
		
		reset( $connections );

		if ( is_empty( $con ) )	return current( $connections );
		
		$connection = isset( $connections[ $con ] ) ? $connections[ $con ] : false;  
		
		return $connection;
	
	}
	
	
	public static function FormTemplate()
	{
		
		if ( self::DefaultFormTemplate() == null || trim( self::DefaultFormTemplate() == "" ) ) return "form";
		
		else return self::DefaultFormTemplate();
		
	}

    public static function DocumentsPath($real_path = true)
    {

        $config_documents_path = static::ValueOf(self::GROUP__MODELS, self::MODELS__DOCUMENTS_PATH);

        $public_path = $real_path ? PUBLICPATH : '';

        if ( ! is_empty( $config_documents_path ) ) $documents_path = Path::Normalize($config_documents_path);

        else if ( ! Path::IsAbsolute( $config_documents_path ) ) $documents_path = $public_path . Path::Normalize( $config_documents_path );

        else $documents_path = $public_path . 'documents';

        return $documents_path;


    }

	public static function DocumentsURL()
	{

        $config_documents_path = static::ValueOf(self::GROUP__MODELS, self::MODELS__DOCUMENTS_PATH);

        $config_documents_url = static::ValueOf(self::GROUP__MODELS, self::MODELS__DOCUMENTS_URL);


        if ( ! is_empty( $config_documents_url ) ) $documents_url = Path::Normalize($config_documents_url);

        else if ( ! Path::IsAbsolute( $config_documents_path ) ) $documents_url = PUBLIC_URL . Path::Normalize( $config_documents_path );

        else $documents_url = PUBLIC_URL . 'documents';

        return $documents_url;

	}

	#endregion
	
}

<?php
/*
*------------------------------------------------------------------------------------------------
* SEMIORBIT - APPLICATION CONTAINER CLASS 		 					 semiorbit.com
*------------------------------------------------------------------------------------------------
*/

namespace Semiorbit\Base;


use Semiorbit\Config\Config;
use Semiorbit\Config\Environment\DotEnv;
use Semiorbit\Debug\AppException;
use Semiorbit\Http\Request;
use Semiorbit\Support\Path;


abstract class Application implements AppInterface
{

    protected static $_Service;

    protected static $_BasePath;

    protected static $_AppPath;

    protected static $_ConfigPath;

    protected static $_PublicPath;


    final public static function Create($public_path = null, $base_path = null, $config_path = null)
    {

        AppManager::Register(static::class);

        if ( ! empty($base_path) ) static::UseBasePath($base_path);

        if ( ! empty($public_path) ) static::UsePublicPath($public_path);

        if ( ! empty($config_path) ) static::UseConfigPath($config_path);


        $myAppService = new AppService( static::BasePath(), static::AppPath(), static::PublicPath(), static::ConfigPath(), static::class );

        static::$_Service = $myAppService;

    }

    final public static function Run($uri = '', $flush_output = true)
    {
        return static::Service()->Run($uri, $flush_output);
    }

    /**
     * @param int $code
     * @param string $message
     * @throws \RuntimeException
     */

    final public static function Abort($code, $message = '')
    {
        throw new \RuntimeException($message, $code);
    }

    /**
     * @param int $code
     * @param string $message
     * @throws AppException
     */

    final public static function Exception($code, $message = '')
    {
        throw new AppException($code);
    }

    /**
     * @return AppService
     */

    final public static function Service()
    {
        if ( ! static::$_Service ) static::Create();

        return static::$_Service;
    }

    /**
     * @return string Application Title
     */

    public static function Title()
    {
        return '';
    }

    /**
     * Get application fully qualified path
     *
     * @return string
     */

    final public static function AppPath()
    {
        return static::$_Service ? static::Service()->AppPath()

            : ( static::$_AppPath ?: ( static::$_AppPath = Path::Normalize( dirname( ( new \ReflectionClass(static::class) )->getFileName() ) ) ) );
    }

    /**
     * Get the fully qualified path to application source/root folder.
     *
     * @return string
     */

    final public static function BasePath()
    {
        return static::$_Service ? static::Service()->BasePath() : ( static::$_BasePath ?: static::UseBasePath() );
    }

    /**
     * Get the fully qualified path to application configuration folder
     *
     * @return string
     */

    final public static function ConfigPath()
    {
        return static::$_Service ? static::Service()->ConfigPath() : ( static::$_ConfigPath ?: static::UseConfigPath() );
    }

    /**
     * Get the fully qualified path to application public folder
     *
     * @param string $path
     * @return string
     */

    final public static function PublicPath($path = '')
    {
        return static::$_Service ? static::Service()->PublicPath($path) :

            ( static::$_PublicPath ?: static::UsePublicPath() ) . ( $path ? Path::Normalize($path, false, null) : '' );
    }

    /**
     * Set the fully qualified path to application source/root folder.<br/>
     * If base path is set to null or an empty string,
     * then base path will be set to <b>app path parent directory</b> by default.<br/><br/>
     *
     * Base path CAN NOT be changed after application service is created.
     * Thus it is good to use onCreate() application event to set base path.
     *
     * @param string $base_path Real path to application source folder
     * @return string Fully qualified base path
     */

    final public static function UseBasePath($base_path = null)
    {

        if ( ! static::$_Service ) {

            if ( empty($base_path) ) {

                static::$_BasePath = Path::Normalize( dirname( dirname( static::AppPath() ) ) );

            } else {

                static::$_BasePath = Path::Normalize( realpath($base_path) );

            }

        }

        return static::$_BasePath;

    }

    final public static function UsePublicPath($public_path = null)
    {

        if ( ! static::$_Service ) {

            if ( empty($public_path) ) {

                static::$_PublicPath = getcwd() ?: realpath('');

            } else {

                static::$_PublicPath = Path::Normalize( realpath($public_path) );

            }

        } else {

            static::$_PublicPath = static::Service()->PublicPath();

        }

        return static::$_PublicPath;

    }

    final public static function UseConfigPath($config_path = null)
    {

        if ( ! static::$_Service ) {

            if ( empty($config_path) ) {

                static::$_ConfigPath = static::BasePath() . 'src/config/';

            } else {

                static::$_ConfigPath = Path::Normalize( realpath($config_path) );

            }

        } else {

            static::$_ConfigPath = static::Service()->LoadConfig($config_path)->ConfigPath();

        }

        return static::$_ConfigPath;

    }


    /**
     * Path to a file in storage folder
     *
     * @param string $path Relative to <b>Storage</b> folder
     * @return string
     */

    final public static function StoragePath($path)
    {
        return static::Service()->StoragePath($path);
    }


    /**
     * Path to a theme file in assets folder
     *
     * @param string $path Relative to <b>Theme</b> folder
     * @param null|string $theme Theme folder name or <u>NULL for default theme</u>
     * @return string
     */

    final public static function AssetPath($path, $theme = null)
    {
        return static::Service()->AssetPath($path, $theme);
    }

    /**
     * Url to a theme file in assets folder
     *
     * @param string $path Relative to <b>Theme</b> folder
     * @param null|string $theme Theme folder name or <u>NULL for default theme</u>
     * @param bool $include_filemtime Add file modification time in url query string
     * @return string
     */

    final public static function Asset($path, $theme = null, $include_filemtime = true)
    {
        return static::Service()->Asset($path, $theme, $include_filemtime);
    }


    /**
     * Loads & populates <b>.env</b> file to <b>$_ENV</b> global-array, if it is not already populated.
     *
     * @param null $key
     * @param null $fallback
     * @return mixed|null if <b>key</b> not set it will return the whole .env list as array,
     *                      <br>otherwise it will return the corresponding value from <b>$_ENV</b> global-array.
     */

    public static function Environment($key = null, $fallback = null)
    {

        if (! isset($_ENV[Config::FWK_ENV_LIST]))

            DotEnv::Import(self::BasePath() . '.env');


        return $key ? ($_ENV[$key] ?? $fallback) : $_ENV[Config::FWK_ENV_LIST];

    }


    /**
     * Triggered before creating app
     * @param AppService $appService
     */

    public static function onInit(AppService $appService)
    {
        //
    }

    /**
     * Triggered after creating app but before running/starting (any request)
     */

    public static function onCreate()
    {
        //
    }

    /**
     * Triggered after running application, but <b>before</b> loading (Start-up request) controller
     */

    public static function onStart()
    {
        //
    }

    /**
     * Triggered <b>before</b> loading controller on startup request only.<br/>
     * NB. Works as a <b>Middleware</b>, for checking auth, permissions etc... when startup request is called.
     *
     * @param Request $request
     */

    public static function onStartup(Request $request)
    {
        //
    }

    /**
     * Triggered <b>before</b> loading controller on all requests other than startup request.<br/>
     * NB. Works as a <b>Middleware</b>, for checking auth, permissions etc... when hmvc request is called.<br>
     * For startup request use <b>onStartup()</b> instead.
     *
     * @param Request $request
     */

    public static function onRequest(Request $request)
    {
        //
    }

    /**
     * Triggered <b>after</b> loading (Start-up request) controller but before firing action.
     *
     * @param Request $request
     */

    public static function onLoad(Request $request)
    {
        //
    }

    /**
     * Triggered after calling (hmvc request), but <b>before</b> loading request controller.<br/>
     * Same as <u>onStart()</u> but fired on <b>HMVC sub-requests</b> only.
     *
     * @param $uri Request URI
     */

    public static function onHmvcRequestStart(&$uri)
    {
        //
    }

    /**
     * Triggered <b>after</b> loading (hmvc request) controller, but before firing action.<br/>
     * Same as <u>onLoad()</u> but fired on <b>HMVC sub-requests</b> only.
     *
     * @param Request $request
     */

    public static function onHmvcRequestLoaded(Request $request)
    {
        //
    }


    /**
     * Triggered after executing requested action.
     * @param $output
     */

    public static function onRun(&$output)
    {
        //
    }

    /**
     * Triggered whenever the script terminated.
     */

    public static function onStop()
    {
        //
    }

    /**
     * Triggered after script executing is complete.
     */

    public static function onFinish()
    {
        //
    }


}
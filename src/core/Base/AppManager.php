<?php
/*
*------------------------------------------------------------------------------------------------
* SEMIORBIT - APPLICATIONS MANAGER CLASS  		 					 semiorbit.com
*------------------------------------------------------------------------------------------------
*/

namespace Semiorbit\Base;


use Semiorbit\Config\Config;
use Semiorbit\Http\Url;
use Semiorbit\Support\Path;

class AppManager
{

    private static $_Applications = array();

    private static $_ActiveApplications = array();

    private static $_ActiveServices = array();

    private static $_MainApplication;


    public static function Register($application)
    {

        if ( ! static::IsRegistered($application) && static::IsApplication($application) ) {

            static::$_Applications[] = $application;

            //Fire onRegister Event

        }

    }

    public static function IsRegistered($application)
    {
        return in_array( $application, static::$_Applications );
    }

    public static function IsApplication($class_name)
    {
        $ref = new \ReflectionClass($class_name);

        return $ref->isSubclassOf(Application::class);
    }

    public static function MarkAsActive($name, $application, AppService $app_service)
    {

        if ( ! static::IsRegistered($application) ) return false;

        static::$_ActiveApplications[$name] = $application;

        static::$_ActiveServices[$name] = $app_service;

        if ( ! static::$_MainApplication ) static::MarkAsMain($name);

        if ( static::IsMainApp($name) ) {

            static::DefineGlobalConstants( static::MainApp() );

        }

        static::CreatePublicSymlinks($app_service);

        return true;

    }

    public static function IsActive($name)
    {
        return isset( static::$_ActiveApplications[$name] );
    }

    public static function MarkAsMain($name)
    {
        static::$_MainApplication = $name;
    }

    public static function IsMainApp($name)
    {
        return static::$_MainApplication == $name;
    }

    /**
     * @return AppService
     */

    public static function MainApp()
    {
        return static::$_ActiveServices[static::$_MainApplication];
    }

    /**
     * @param string $name Application FullName
     * @return AppService
     */

    public static function App($name)
    {
        return static::$_ActiveServices[$name];
    }

    public static function CallMainApp($method, $pms = array())
    {
        return static::Call( static::MainApp()->ApplicationClass(), $method, $pms );
    }

    public static function Call($application, $method, $pms = array())
    {
        return call_user_func_array( array( '\\' . $application, $method ), $pms );
    }

    protected static function CreatePublicSymlinks(AppService $app)
    {

        if (!function_exists('symlink')) return;

        $theme = Config::Theme();

        $assets_dir = $app->AssetsDirectory();

        $links = array(
            'ext' => $app->BasePath('ext'),
            'documents' => $app->BasePath('storage/documents'),
            $assets_dir . '/' . $theme . '/css' => $app->AssetPath('css', $theme),
            $assets_dir . '/' . $theme . '/js' => $app->AssetPath('js', $theme),
            $assets_dir . '/' . $theme . '/fonts' => $app->AssetPath('fonts', $theme),
            $assets_dir . '/' . $theme . '/images' => $app->AssetPath('images', $theme),
            $assets_dir . '/' . $theme . '/videos' => $app->AssetPath('videos', $theme)
        );

        foreach ($links as $link => $target) {


            $link_path = $app->PublicPath($link);

            if (file_exists($link_path)) continue;

            if (file_exists($target)) {

                $link_dir_path = dirname($link_path);


                if (!file_exists($link_dir_path)) mkdir($link_dir_path, 0755, $link_dir_path);


                symlink($target, $link_path);
            }
            
        }
    }

    protected static function DefineGlobalConstants(AppService $app)
    {


        define('APPPATH', $app->AppPath());

        define('BASEPATH', $app->BasePath());

        define('PUBLICPATH', $app->PublicPath());

        define('PUBLIC_DIR', $app->PublicDirectory());

        ## BASE_URL ##
        define('BASE_URL', Url::BaseUrl());

        ## BASE_DIR ##
        define('BASE_DIR', Url::BaseUrl(false, false, true));

        ## PUBLIC_URL ##
        define('PUBLIC_URL', $app->PublicUrl());

        ## START-UP THEME ##
        define('THEME', PUBLIC_URL . $app->AssetsDirectory() . '/' . Path::Normalize( Config::Theme() ) );

        ## EXT ##
        define('EXT', PUBLIC_URL . Path::Normalize( Config::JsExtDir() ));




        /*
         *---------------------------------------------------------------
         * START SESSION
         *---------------------------------------------------------------
         */

        \Semiorbit\Session\Session::Start( md5( AppManager::MainApp()->Name() ) );


    }
    
}
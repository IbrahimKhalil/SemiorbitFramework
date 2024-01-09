<?php 
/*
*------------------------------------------------------------------------------------------------
* SEMIORBIT - APPLICATION INSTANCE CLASS  		 					 semiorbit.com
*------------------------------------------------------------------------------------------------
*/

namespace Semiorbit\Base;


use http\Exception;
use Semiorbit\Component\Finder;
use Semiorbit\Component\Services;
use Semiorbit\Config\Config;
use Semiorbit\Http\Request;
use Semiorbit\Http\Response;
use Semiorbit\Http\Url;
use Semiorbit\Output\Output;
use Semiorbit\Support\Path;

class AppService
{

    protected $_Name;

    protected $_AppNamespace;


    protected $_Application;

    protected $_BasePath;

    protected $_ConfigPath;

    protected $_RoutesPath;

    protected $_AppPath;

    protected $_PublicPath;

    protected $_StoragePath;

    protected  $_DatabasePath;

    protected $_AssetsPath;

    protected $_LangPath;

    protected $_ViewsPath;

    protected $_ControllersPath;

    protected $_ModelsPath;

    protected $_ThemePath = array();


    protected $_AssetsDirectory = 'assets';

    protected $_PublicDirectory;

    protected $_PublicUrl;

    protected $_ThemeUrl = array();


    protected $_ControllersNamespace;

    protected $_ModelsNamespace;


    protected $_IsStartUpRequest = true;

    protected $_Composer;


    public function __construct($base_path, $app_path, $public_path, $config_path = null, $application = null)
    {
        $this->StartApplication($base_path, $app_path, $public_path, $config_path, $application);
    }

    public function Name()
    {
        if ( ! $this->_Name ) $this->UseName();

        return $this->_Name;
    }

    public function UseName($name = '')
    {
        if ( ! $name ) $this->_Name = $this->Composer('name');

        return $this;
    }

    /**
     * Relate this app service to an application class
     *
     * @param string $application Application class name
     * @return $this
     */

    public function UseApplication($application)
    {
        $this->_Application = $application;

        return $this;
    }

    public function ApplicationClass()
    {
        return $this->_Application;
    }

    /**
     * Setup application paths and configurations
     *
     * @param string $base_path
     * @param string $app_path
     * @param string $public_path
     * @param string $config_path
     * @param string|null $application
     * @return $this
     */

    public function StartApplication($base_path, $app_path, $public_path, $config_path = null, $application = null)
    {

        $this->UseApplication($application);

        //Fire onInit Event
        AppManager::Call( $application, 'onInit', [$this] );


        $this->_BasePath = $base_path;

        $this->_AppPath = $app_path;

        $this->_PublicPath = $public_path;

        $this->LoadConfig($config_path)->UseRoutesPath();

        if ( $application )

            AppManager::MarkAsActive( Config::AppNamespace(), $application, $this );


        Services::Register($this);

        //Fire onCreate Event
        AppManager::Call( $application, 'onCreate' );

        return $this;

    }

    /**
     * Get the fully qualified path to application source/root folder.
     *
     * @param string $path
     * @return string
     */

    public function BasePath($path = '')
    {
        return $this->_BasePath . ( $path ? Path::Normalize($path, false, null) : '' );
    }

    /**
     * Get application fully qualified path
     *
     * @param string $path
     * @return string
     */

    public function AppPath($path = '')
    {
        return $this->_AppPath . ( $path ? Path::Normalize($path, false, null) : '' );
    }

    /**
     * Get the fully qualified path to application configuration folder
     *
     * @param string $path
     * @return string
     */

    public function ConfigPath($path = '')
    {
        return $this->_ConfigPath . ( $path ? Path::Normalize($path, false, null) : '' );
    }



    /**
     * Get the fully qualified path to application routes folder
     *
     * @param string $path
     * @return string
     */

    public function RoutesPath($path = '')
    {
        return $this->_RoutesPath . ( $path ? Path::Normalize($path, false, null) : '' );
    }

    public function UseRoutesPath($routes_path = null)
    {
        $this->_RoutesPath = $routes_path ?: $this->BasePath('routes');

        return $this;
    }


    /**
     * Get the fully qualified path to application public folder
     *
     * @param string $path
     * @return string
     */

    public function PublicPath($path = '')
    {
        return $this->_PublicPath . ( $path ? Path::Normalize($path, false, null) : '' );
    }

    public function UsePublicPath($public_path)
    {
        $this->_PublicPath = Path::Normalize( realpath($public_path) );

        return $this;
    }

    /**
     * Get the fully qualified path to public assets folder
     *
     * @param string $path
     * @return string
     */

    public function PublicUrl($path = '')
    {
        if ( ! $this->_PublicUrl ) $this->UsePublicUrl();

        return $this->_PublicUrl . ( $path ? Path::Normalize($path, false) : '' );
    }

    public function PublicDirectory()
    {
        return $this->_PublicDirectory ?: $this->_PublicDirectory = Path::PublicDirectory( $this->PublicPath() );
    }

    public function UsePublicUrl($url = null)
    {

        if ( ! $url ) {

            $this->_PublicUrl = Url::BaseUrl() . $this->PublicDirectory();

        } else {

            $this->_PublicUrl = $url;

        }

        return $this;

    }

    public function ControllerPath($controller_name)
    {
        return Path::ClassFileName( strstr($controller_name, '\\') ? $controller_name :

            $this->ControllerFullyQualifiedName($controller_name) );
    }

    public function ControllersPath()
    {
        if ( ! $this->_ControllersPath ) $this->UseControllersPath();

        return $this->_ControllersPath;
    }

    public function UseControllersPath($path = '')
    {

        if ( ! $path ) $path = Config::ControllersDir();

        $this->_ControllersPath = Path::Normalize( realpath( $this->AppPath($path) ) );

        return $this;

    }

    public function ModelPath($model_name)
    {
        return Path::ClassFileName( strstr($model_name, '\\') ? $model_name :

            $this->ModelFullyQualifiedName($model_name) );
    }

    public function ModelsPath()
    {
        if ( ! $this->_ModelsPath ) $this->UseModelsPath();

        return $this->_ModelsPath;
    }

    public function UseModelsPath($path = '')
    {

        if ( ! $path ) $path = Config::ModelsDir();

        $this->_ModelsPath = Path::Normalize( realpath( $this->AppPath($path) ) );

        return $this;

    }

    /**
     * Path to assets folder
     *
     * @param string $path
     * @return string
     */

    public function AssetsPath($path = '')
    {
        if (! $this->_AssetsPath) $this->UseAssetsPath();

        return $this->_AssetsPath . ( $path ? Path::Normalize($path, false, null) : '' );
    }

    /**
     * Path to a theme file in assets folder
     *
     * @param string $path Relative to <b>Theme</b> folder
     * @param null|string $theme Theme folder name or <u>NULL for default theme</u>
     * @return string
     */

    public function AssetPath($path, $theme = null)
    {
        return $this->ThemePath($theme) . ( $path ? Path::Normalize($path, false, null) : '' );
    }

    public function ThemePath($theme = null)
    {

        if ($theme === null) $theme = Config::Theme();

        if (! isset($this->_ThemePath[$theme])) $this->_ThemePath[$theme] = Path::Normalize( $this->AssetsPath($theme) );

        return $this->_ThemePath[$theme];

    }

    public function StoragePath($path = null)
    {

        return ($this->_StoragePath ?:

            $this->_StoragePath = $this->BasePath('storage'))

             . ( $path ? Path::Normalize($path) : '' );

    }

    public function DatabasePath($path = null)
    {

        return ($this->_DatabasePath ?:

            $this->_DatabasePath = $this->BasePath('src/database'))

             . ( $path ? Path::Normalize($path) : '' );

    }

    public function UseAssetsPath($assets_path = '')
    {

        if (! $assets_path) $assets_path = $this->BasePath('src/' . $this->AssetsDirectory());

        $this->_AssetsPath = Path::Normalize( realpath($assets_path) );

        $this->_AssetsDirectory = basename($this->_AssetsPath);

        return $this;

    }

    public function AssetsDirectory()
    {
        if (! $this->_AssetsDirectory && ! $this->_AssetsPath) $this->_AssetsDirectory = 'assets';

        return $this->_AssetsDirectory;
    }

    /**
     * Url to a theme file in assets folder
     *
     * @param string $path Relative to <b>Theme</b> folder
     * @param null|string $theme Theme folder name or <u>NULL for default theme</u>
     * @param bool $include_filemtime Add file modification time in url query string
     * @return string
     */

    public function Asset($path, $theme = null, $include_filemtime = true)
    {
        $file_path = $this->AssetPath($path, $theme);

        return $this->ThemeUrl($theme) . Path::Normalize($path, false, null)

                    . ($include_filemtime && file_exists($file_path) ? '?' . filemtime($file_path) : '');
    }

    public function ThemeUrl($theme = null)
    {

        if ($theme === null) $theme = Config::Theme();

        if (! isset($this->_ThemeUrl[$theme]))

            $this->_ThemeUrl[$theme] = $this->PublicUrl(Path::Normalize($this->_AssetsDirectory, false, false) . Path::Normalize($theme, true, true));

        return $this->_ThemeUrl[$theme];

    }

    /**
     * Select and set application configurations
     *
     * @param string $config_path
     * @return $this
     */

    public function LoadConfig($config_path)
    {

        if ( empty($config_path) ) $config_path = $this->BasePath('config');

        $this->_ConfigPath = Path::Normalize( realpath($config_path) );


        Config::Load();

        return $this;

    }



    public function UseAppNamespace($namespace = '')
    {

        if ($namespace) {

            $this->_AppNamespace = Path::NormalizeNamespace($namespace, true);

        } else {

            # This code is inspired by Laravel Application::getNamespace() source code
            # https://github.com/laravel/framework/blob/5.3/src/Illuminate/Foundation/Application.php

            foreach ((array)$this->Composer('autoload.psr-4') as $namespace => $path) {

                foreach ((array)$path as $path_choice) {

                    if ($this->AppPath() == $this->BasePath($path_choice)) {

                        $this->_AppNamespace = Path::NormalizeNamespace($namespace, true);

                    }
                }
            }

        }


        $this->ResetControllersNamespace();

        $this->ResetModelsNamespace();

        return $this;

    }

    public function AppNamespace()
    {

        if ( ! $this->_AppNamespace ) $this->UseAppNamespace();

        return $this->_AppNamespace;

    }

    public function ResetControllersNamespace()
    {

        $controllers_dir = '';

        if ( substr($this->ControllersPath(), 0, strlen($this->AppPath()) ) == $this->AppPath()) {

            $controllers_dir = substr( $this->ControllersPath(), strlen($this->AppPath()) );

        }

        $this->_ControllersNamespace = $this->AppNamespace() . ( $controllers_dir ? Path::NormalizeNamespace( $controllers_dir, true, false ) : '' );

    }

    public function ResetModelsNamespace()
    {

        $models_dir = '';

        if ( substr($this->ModelsPath(), 0, strlen($this->AppPath()) ) == $this->AppPath()) {

            $models_dir = substr( $this->ModelsPath(), strlen($this->AppPath()) );

        }

        $this->_ModelsNamespace = $this->AppNamespace() . ( $models_dir ? Path::NormalizeNamespace( $models_dir, true, false ) : '' );

    }

    public function ControllersNamespace()
    {
        if ( ! $this->_ControllersPath ) $this->ResetControllersNamespace();

        return $this->_ControllersNamespace;
    }

    public function ModelsNamespace()
    {
        if ( ! $this->_ModelsNamespace ) $this->ResetModelsNamespace();

        return $this->_ModelsNamespace;
    }

    public function ControllerFullyQualifiedName($controller_name, $add_suffix = true)
    {
        return $this->ControllersNamespace()

                        . Path::NormalizeNamespace( $controller_name, true, false )

                        . ( $add_suffix ? Config::ControllerSuffix() : '' );
    }

    public function ModelFullyQualifiedName($model_name)
    {
        return $this->ModelsNamespace()

                        . Path::NormalizeNamespace( $model_name, true, false );
    }


    public function Run($uri = '', $flush_output = true) {

        ob_start();

        try {


            if ( $this->_IsStartUpRequest ) {


                $request = Request::Startup()

                    ->UseApplicationClass($this->ApplicationClass());

                //This property should be turned on
                //before loading "Start up request", because maybe some other
                //sub-request sent on "Start up request" loading process.

                $this->_IsStartUpRequest = false;

                //Fire onStart Event

                AppManager::Call($this->ApplicationClass(), 'onStart');

                Request::Startup()->Load();


                //Fire onLoad Event

                AppManager::Call($this->ApplicationClass(), 'onLoad', [$request]);


                Url::setPreviousPage();


            } else {


                AppManager::Call($this->ApplicationClass(), 'onHmvcRequestStart', [&$uri]);


                // Empty HMVC request

                if (is_empty($uri)) { @ob_end_clean(); return null; }


                $request = new Request($uri);


                $request

                    ->UseApplicationClass($this->ApplicationClass())

                    ->Load();

                //Fire on request loaded event

                AppManager::Call($this->ApplicationClass(), 'onHmvcRequestLoaded', [$request]);


            }


            /*
            echo '<div style="position:absolute;bottom:0px;left:0px;text-align:left;">';
            echo "Lang:     "   .   $request->Lang  .                "<br />";
            echo "Class:    "   .   $request->Controller['class']  . "<br />";
            echo "Action:   "   .   $request->Action['method']  .    "<br />";
            echo "ID:       "   .   $request->ID    .                "<br />";
            echo "path:     "   .   $request->PathInfo  .            "<br />";
            echo '</div>';
            */




            // Call Action

            $output = $request->CallAction();


            Request::LastRun($request);

            $buffer = ob_get_contents();


            @ob_end_clean();

            $output = $buffer . $output;

            //Fire onRun Event

            AppManager::Call($this->ApplicationClass(), 'onRun', [&$output]);


        } catch (\Exception $e) {

            ob_end_clean();

            $output = Response::SendException($e, false);

        }


        if ( Config::SanitizeOutput() ) $output = Output::Sanitize($output);

        if ($flush_output) echo $output;

        return $output;

    }

    /**
     * Find value of an option that is set in <b>composer.json</b> file of this application
     *
     * @param string $selector Key path separated by dots
     * @param bool $force_reload Force reloading composer.json file
     * @return mixed
     */

    public function Composer($selector = '', $force_reload = false)
    {

        if ( (!$this->_Composer) || $force_reload ) {

            $composer_json = file_get_contents($this->BasePath('composer.json')) ?: die('<h1>Composer.json File not Found!</h1>');

            $this->_Composer = json_decode($composer_json, true) ?: die('<h1>Can not load Composer.json!</h1>');

        }


        if ( ! $selector ) return $this->_Composer;

        return Path::Value($this->_Composer, $selector);

    }


}

<?php
/*
*------------------------------------------------------------------------------------------------
* LOADER - SEMIORBIT CLASS AUTO LOADER			 					 semiorbit.com
*------------------------------------------------------------------------------------------------
* Ref. Part of this class is built on PSR-0
* http://www.php-fig.org/psr/psr-0/
* 
*/

namespace Semiorbit\Component;



use Semiorbit\Config\CFG;


class Loader
{

    public static function AutoLoader($class)
    {

        if (class_exists($class)) return false;


        /*if ((ends_with($class, CFG::$ControllerSuffix) || ends_with($class, 'Controller')) &&

            (!ends_with($class, '\\' . CFG::$ControllerSuffix) && !ends_with($class, '\\Controller')) &&

            ($class != CFG::$ControllerSuffix && $class != 'Controller')

        ) return static::LoadControllerClass( $class );


        if (static::LoadNS($class)) return true;


        if (static::LoadGlobalClasses($class)) return true;
        */

        // SEARCH IN ALIASES ------------------------------

        foreach (CFG::$ClassAlias as $ns => $class_arr) {


            if (in_array($class, $class_arr)) {


                    if ( ! class_exists($class) ) class_alias($ns, $class);

                    return true;


            }

        }

        //$fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

        //require $fileName;

        return false;


    }


    private static function LoadNS($class)
    {

        /*
        $class_name = ltrim($class, '\\');


        $last_ns_pos = strrpos($class_name, '\\');

        if ($last_ns_pos) {

            $namespace = substr($class_name, 0, $last_ns_pos);

            $class_name = substr($class_name, $last_ns_pos + 1);

            $file_path = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;

            if (starts_with($file_path, 'Semiorbit' . DIRECTORY_SEPARATOR)) {


                $file_path = str_replace_first('Semiorbit' . DIRECTORY_SEPARATOR, 'core' . DIRECTORY_SEPARATOR, $file_path);

                if (class_exists($namespace . '\\' . $class_name)) return true;

                Loader::Import($class_name, $file_path, '.php', $namespace);

                //echo "LOADED --- ". $class . " --- <hr/>";

                return true;

            } elseif (starts_with($file_path, CFG::$AppNamespace . DIRECTORY_SEPARATOR)) {


                $file_path = str_replace_first(CFG::$AppNamespace . DIRECTORY_SEPARATOR, '', $file_path);

                if (class_exists($namespace . '\\' . $class_name)) return true;

                Loader::Import($class_name, $file_path, CFG::$AppClassExt, $namespace);

                //echo "LOADED --- ". $class . " --- <hr/>";

                return true;

            }

        }

        return false;
        */

    }

    private static function LoadGlobalClasses($class)
    {

        /*
        if (class_exists($class)) return true;

        //FIRST MODELS

        $path = Finder::LookFor($class, Finder::Models);

        if ($path) {

            if (file_exists($path['path'])) {

                require "{$path['path']}";

                //echo "LOADED --- ". $class . " --- <hr/>";

                return true;

            }

        }

        //THEN OTHER GLOBAL CLASSES

        foreach (CFG::$AutoloadClasses as $k => $global_dir) {

            $file_path = rtrim(str_replace_first('app/', APPPATH, $global_dir['dir']), '/') . '/' . $class . $global_dir['ext'];

            if (!empty($file_path) && file_exists($file_path)) {

                require "{$file_path}";

                return true;

            }

        }

        return false;

        **/

    }

    private static function LoadControllerClass($class)
    {
        /**

        if (class_exists($class)) return true;

        $path = Finder::LookFor( \Semiorbit\Http\Controller::Name( $class ), Finder::Controllers );

        if ($path) {

            if (file_exists($path['path'])) {

                require "{$path['path']}";

                //echo "LOADED --- ". $class . " --- <hr/>";

                return true;

            }

        }

        return false;
         *
         * */
    }

    public static function CallAppClass($method, $pms = array())
    {

       // $app_class = static::LoadAppClass();

        //return  $app_class ?

          //  call_user_func_array( array( '\\' . $app_class, $method ), $pms ) : false;

    }

    public static function LoadAppClass()
    {

        /*
        static $app_class;

        if ( ! ( $app_class && $app_class == CFG::$AppClass ) ) {


            if ( CFG::$AppClass == "" ) $app_class = false;

            $path = APPPATH . CFG::$AppClass . '.php';


            file_exists( $path ) ? include_once $path : $app_class = false;

            $app_class = class_exists( CFG::$AppClass ) ? CFG::$AppClass : false;

        }

        return $app_class;
        */

    }



    /**
     * IMPORT
     *
     * This function imports the required class.
     *
     * @access    public
     * @param    string    $class name to import
     * @param    string    $root to class file
     * @param    string    $ext file extension
     * @param    string    $namespace prefix eg. semiorbit_uri
     * @return    object
     */

    public static function Import($class, $root = 'core', $ext = '.php', $namespace = 'Semiorbit')
    {

        if ($namespace != '') {
            $namespace = rtrim($namespace, "\\") . '\\';
        }

        $root = rtrim($root, '/');

        ## -----------------------------------------------------------
        ## Check the CLASS in BASEPATH/$root THEN >>>
        ## Check the CLASS in FW/$root "BUILTIN"
        ## -----------------------------------------------------------


        foreach (array(BASEPATH, FW) as $path) {

            $class_path = $path . $root . '/' . $class . $ext;

            if (file_exists($class_path)) {

                if (class_exists($namespace . $class) === false) {
                    /** @noinspection PhpIncludeInspection */
                    require "{$class_path}";
                }

                break;
            }
        }

        ## -----------------------------------------------------------
        ## CLASS was not found => 404
        ## -----------------------------------------------------------

        if (class_exists($namespace . $class) === false) {

            if (interface_exists($namespace . $class) === false) {

                if (trait_exists($namespace . $class) === false) {


                    \Semiorbit\Debug\Log::Inline()->Trace(1)->TraceStartIndex(6)->Info("Failed to Import {$namespace}{$class}", $class);

                    die("<h3>Failed to Import required object!</h3>");

                }

            }

        } else {

            ## -----------------------------------------------------------
            ## CREATE CLASS ALIASES
            ## -----------------------------------------------------------

            if (isset(CFG::$ClassAlias[$namespace . $class])) :

                $aliases = CFG::$ClassAlias[$namespace . $class];

                foreach ($aliases as $alias) {

                    if (!class_exists($alias))

                        class_alias($namespace . $class, $alias);

                }

            endif;

        }

    }


}
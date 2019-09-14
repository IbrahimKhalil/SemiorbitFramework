<?php
/*
*------------------------------------------------------------------------------------------------
* FINDER - SEMIORBIT SKELETON DIR MANAGEMENT			 				 semiorbit.com
*------------------------------------------------------------------------------------------------
*
*/

namespace Semiorbit\Component;




use Semiorbit\Base\AppManager;
use Semiorbit\Config\CFG;


class Finder
{
	const Controllers = 'Controllers';

	const Views = 'Views';

	const Models = 'Models';

	const Lang = 'LangPath';



	public static function APP_FilePath($class, $directory, $class_as_file_name = false)
	{

		if ( $class == '' ) return false;

        $dir = isset( CFG::${$directory} ) ? trim( CFG::${$directory}, '/' ) : $directory;

        $ext = isset( CFG::${$directory.'Ext'} ) ? 	'.' . trim( CFG::${$directory . 'Ext'}, '.' ) : '';

		$path = BASEPATH . $dir . '/' . $class . $ext;
			
		if ($class_as_file_name === true) $path = BASEPATH . $dir . '/' . $class;


        return file_exists( $path ) ?  $path : false;

	}


	public static function FW_FilePath($class, $directory, $class_as_file_name = false)
	{

		if ( $class == '' ) return false;

        $dir = isset( CFG::$DefConfig[ $directory ] ) ? trim( CFG::$DefConfig[ $directory ], '/') : $directory;

        if ( $dir == Finder::Controllers ) $dir = "core/Controllers";

        $ext = isset( CFG::$DefConfig[ $directory . 'Ext' ] ) ? '.' . trim( CFG::$DefConfig[ $directory . 'Ext' ], '.' ) : '';

		$path = FW . $dir . '/' . strtolower( $class ) . $ext;

		if ( $class_as_file_name === true ) $path = FW . $dir . '/' . $class;


        return file_exists( $path ) ?  $path : false;

	}

    /**
     * @param $selector
     * @param $in_directory
     * @param bool $class_as_file_name
     * @param bool $project_only
     * @return array|bool|FinderResult
     */

    public static function LookFor($selector, $in_directory, $class_as_file_name = false, $project_only = false)
    {

        ## -----------------------------------------------------------
        ## Check the CLASS in BASEPATH/$root THEN >>>
        ## Check the CLASS in FW/$root "BUILTIN"
        ## -----------------------------------------------------------

        if (!is_array($selector)) $selector = array($selector);

        if (!is_array($in_directory)) $in_directory = array($in_directory);

        foreach ($selector as $my_class) {

            foreach ($in_directory as $dir) {


                if ( $dir == Finder::Controllers ) {

                    $controller = static::LookForController($my_class);

                    if ($controller) return $controller;

                } elseif ( $dir == Finder::Models ) {

                    $selector = static::LookForModel($my_class);

                    if ($selector) return $selector;

                } else {

                    $class_name = $my_class;


                    $APP_FP = Finder::APP_FilePath($class_name, $dir, $class_as_file_name);

                    if ($APP_FP) return new FinderResult( array('class' => $class_name, 'path' => $APP_FP, 'dir' => $dir, 'selector' => $my_class) );


                    if (!$project_only) :

                        $FW_FP = Finder::FW_FilePath($my_class, $dir, $class_as_file_name);

                        if ($FW_FP) return new FinderResult( array('class' => $class_name, 'path' => $FW_FP, 'dir' => $dir, 'selector' => $my_class) );

                    endif;

                }

            }

        }

        // NOT FOUND
        return false;

    }

    /**
     * @param $selector
     * @param bool $application_only
     * @return bool|FinderResult
     *
     */
    public static function LookForController($selector, $application_only = false)
    {

        foreach ( (array) $selector as $controller_name ) {

            $class_name = AppManager::MainApp()->ControllerFullyQualifiedName($controller_name);

            if ( class_exists( $class_name ) ) {

                return new FinderResult( array('class' => $class_name,

                    'dir' => Finder::Controllers, 'selector' => $controller_name) );

            }

            if ( ! $application_only ) {

                $class_name = "\\Semiorbit\\Controllers\\{$controller_name}Controller";

                if ( class_exists( $class_name ) ) {

                    return new FinderResult( array('class' => $class_name,

                        'dir' => Finder::Controllers, 'selector' => $controller_name) );

                }

            }

        }

        return false;

    }

    /**
     * @param $selector
     * @return bool|FinderResult
     */

    public static function LookForModel($selector)
    {

        foreach ( (array) $selector as $model_name ) {

            $class_name = AppManager::MainApp()->ModelFullyQualifiedName($model_name);

            if ( class_exists( $class_name ) ) {

                return new FinderResult( array('class' => $class_name,

                    'dir' => Finder::Models, 'selector' => $model_name) );

            }

        }

        return false;

    }


    /**
     * abort
     *
     * prints custom err pages in he output when needed OR return
     * a string containing the custom err page.
     *
     *
     * @access   public
     * @param    mixed  $err_nom  err_nom ex. 404, 405, etc ...
     * @return   string
     */

    public static function Error($err_nom)
    {

        ## -----------------------------------------------------------
        ## Check ERROR FILE in BASEPATH/err/ THEN >>>
        ## Check ERROR FILE in FW/err/ "DEFAULT"
        ## -----------------------------------------------------------

        $err = null;

        foreach (array(BASEPATH . CFG::$Views . 'errors/', FW . 'views/errors/') as $path) {

            $err_file = $path . $err_nom . CFG::$ViewsExt;

            if (!empty($err_file) && file_exists($err_file)) {

                /** @noinspection PhpIncludeInspection */
                $err = include "{$err_file}";

            }

        }

        ## -----------------------------------------------------------
        ## If error file not found just print error number
        ## -----------------------------------------------------------

        if (! $err) {
            $err = "<h1>Error {$err_nom}!</h1>";
        }

        return $err;

    }







}

<?php
/*
*------------------------------------------------------------------------------------------------
* FINDER - SEMIORBIT SKELETON DIR MANAGEMENT			 				 semiorbit.com
*------------------------------------------------------------------------------------------------
*
*/

namespace Semiorbit\Component;




use Semiorbit\Base\AppManager;
use Semiorbit\Config\Config;


class Finder
{

    const Controllers = 'controllers';

	const Views = 'views';

	const Models = 'models';

	const Lang = 'lang';



	public static function APP_FilePath($class, $directory, $class_as_file_name = false)
	{

		if ( $class == '' ) return false;

        $dir = Config::StructureDirectory($directory, $directory);

        $ext = Config::StructureExtension($directory, Config::FrameworkConfig()[$directory . '_ext']);

		$path = BASEPATH . $dir . '/' . $class . $ext;
			
		if ($class_as_file_name === true) $path = BASEPATH . $dir . '/' . $class;


        return file_exists( $path ) ?  $path : false;

	}


	public static function FW_FilePath($class, $directory, $class_as_file_name = false)
	{

		if ( $class == '' ) return false;

        $dir = Config::FrameworkConfig()[ $directory ] ?? $directory;

        $ext = isset( Config::FrameworkConfig()[ $directory . '_ext' ] ) ? '.' . Config::FrameworkConfig()[ $directory . '_ext' ] : '';

		$path = $class_as_file_name === true ?

            FW . $dir . '/' . $class :

            FW . $dir . '/' . strtolower( $class ) . $ext;


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



}

<?php
/*
*------------------------------------------------------------------------------------------------
* SEMIORBIT - WIDGET Builder    					 					 semiorbit.com
*------------------------------------------------------------------------------------------------
*/

namespace Semiorbit\Output;


use Semiorbit\Component\Finder;
use Semiorbit\Config\Config;


class Widget extends ViewBase
{


    /**
     * Widget file path or FALSE if not found
     *
     * @param string $widget Widget name
     * @return bool|string
     */

    public static function FindPath($widget)
    {

        $view_path = Render::CacheViewPath( Config::ViewsDir() . '@Widget_' . $widget) ?: call_user_func( function () use ( $widget ) {

            $view_ext =  Config::WidgetExt();

            $view_path = Finder::LookFor($widget . $view_ext, Finder::Views, true);

            Render::CacheViewPath( Config::ViewsDir() . '@Widget_' . $widget . $view_ext, $view_path);

            return $view_path;

        });

        return $view_path ? $view_path['path'] : false;

    }



}
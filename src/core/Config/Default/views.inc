<?php

use Semiorbit\Config\Config;

return [

    /*
    | -----------------------------------------------------------
    | VIEWS
    | -----------------------------------------------------------
    */

    ## Path to template ##

    Config::VIEWS__DIR => "src/views",

    ## File Extension ##

    Config::VIEWS__EXT => ".phtml",

    ## Container File FullName ##

    Config::VIEWS__DEFAULT_LAYOUT => "layout",

    ## Page Title Separator::  My Project - My Page ##

    Config::VIEWS__PAGE_TITLE_SEPARATOR => " - ",

    ## WIDGETS ##

    Config::VIEWS__WIDGET_EXT => ".widget.phtml",

    ## BOX ##

    Config::VIEWS__BOX_EXT => ".box.phtml",


    /*
    | -----------------------------------------------------------
    | THEME
    | -----------------------------------------------------------
    */

    ## Path to theme in public folder ##

    Config::VIEWS__THEME => "theme"

];
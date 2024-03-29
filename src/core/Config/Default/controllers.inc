<?php

use Semiorbit\Config\Config;

return [


    /*
    | -----------------------------------------------------------
    | CONTROLLERS
    | -----------------------------------------------------------
    */

	## Path ##
	Config::CONTROLLERS_DIR => "Http",

	## ClassName_Suffix ##
	Config::CONTROLLERS_SUFFIX => "Ctrl",

	## File Extension ##
    ## Read only: don't change ##

    Config::CONTROLLERS_EXT => ".php",


    /*
    | -----------------------------------------------------------
    | CUSTOMIZE BASIC URI PARAMETERS NAMING
    | -----------------------------------------------------------
    */

    Config::CONTROLLERS__BASIC_PARAM_CUSTOM_NAMING => [

        'lang' => "lang",

        'id' => "id",

        'layout' => 'layout'

    ]


];
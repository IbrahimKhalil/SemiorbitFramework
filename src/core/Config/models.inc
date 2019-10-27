<?php

use Semiorbit\Config\Config;

return [

    /*
    | -----------------------------------------------------------
    | Models
    | -----------------------------------------------------------
    */

	## Path ##

	Config::MODELS__DIR => "",

	## File Extension ##

	Config::MODELS__EXT => ".php",


    /*
    | -----------------------------------------------------------
    | UPLOAD DOCUMENTS & IMAGES FOLDER SETTINGS
    | -----------------------------------------------------------
    |
    | Relative path to upload directory in PUBLICPATH root
    | or absolute path to upload directory anywhere else.
    | -----------------------------------------------------------
    */

    Config::MODELS__DOCUMENTS_PATH => "documents",



    /*
    | -----------------------------------------------------------
    | Leave empty to auto generate url to documents folder
    | in PUBLIC directory. But if "documents path" is not relative to
    | PUBLIC directory this option should be explicitly defined.
    | -----------------------------------------------------------
    */

    Config::MODELS__DOCUMENTS_URL => ""

];
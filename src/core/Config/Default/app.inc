<?php

use Semiorbit\Config\Config;

return [


    /*
    | -----------------------------------------------------------
    | LANGUAGES LIST
    |
    | Please limit this array to your available languages
    | array('default' => 'en', 'de', 'ar-eg', 'es', 'pt-br'),
    | -----------------------------------------------------------
    */

    Config::APP__LANGUAGES => ['default' => 'en'],

    

    /*
    | -----------------------------------------------------------
    | Environment
    | -----------------------------------------------------------
    */

    Config::APP__ENVIRONMENT => env('APP_ENV', Config::ENV_PRODUCTION),

    Config::APP__DEBUG_MODE => env('APP_DEBUG', false),



    /*
    | -----------------------------------------------------------
    | APP NAMESPACE
    | -----------------------------------------------------------
    */
    
    Config::APP__NAMESPACE => "App",

    
    
    /*
    | -----------------------------------------------------------
    | App Class
    | -----------------------------------------------------------
    */

    Config::APP__CLASS => "App",



    /*
    | -----------------------------------------------------------
    | Main Controller
    |
    | Home Page: is where to go  
    | by default the FW will look for (in order):
    | ('index', 'home', 'main'), if not found then
    | FW/controllers/main.class.php will be used as
    | Main Controller. This in its turn will look for
    | TPL/[index, home, main].[tpl_ext] as a home page view
    | If not found it will Render FW/views/main.tpl.inc as home.
    | -----------------------------------------------------------
    */

    Config::APP__MAIN_PAGE => "Main",
    
    

    /*
    | -----------------------------------------------------------
    | MINIFY HTML OUTPUT
    | -----------------------------------------------------------
    */

    Config::APP__SANITIZE_OUTPUT => false,

    
    

    /*
    | -----------------------------------------------------------
    | Index file
    | -----------------------------------------------------------
    |
    | NOT REQUIRED // if => "" then $_SERVER['SCRIPT_NAME'] will be used
    | eg. /myProject/index.php
    | -----------------------------------------------------------
    */

    Config::APP_INDEX_FILE_PATH => "",
    

    
    /*
    | -----------------------------------------------------------
    | HTML/JS EXTERNAL PLUGINS OR THIRD PARTY/VENDOR TOOLS
    | -----------------------------------------------------------
    |
    | PathInfo to ext resources in public folder
    | -----------------------------------------------------------
    */


    Config::APP__JS_EXT_DIR => "ext",



    /*
    | -----------------------------------------------------------
    | CLASS ALIAS LIST
    | -----------------------------------------------------------
    */

    Config::APP__CLASS_ALIAS => [

        // Example

        // 'Semiorbit\\Field\\DataType' =>>  array('DataType')
    ],

    
    

];

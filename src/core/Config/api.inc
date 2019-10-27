<?php

use Semiorbit\Config\Config;

return [


    /*
    | -----------------------------------------------------------
    | API
    | -----------------------------------------------------------
    |
    | Is Project an Api?
    |
    | True: all controllers should be restful controllers, and no sub directory/ version directory is needed
    | False: Api controllers should be located in
    |        Http/{$ApiControllers} sub directory. Version sub directory is needed in this case
    | -----------------------------------------------------------
    */

    Config::API__MODE => false,



    /*
    | -----------------------------------------------------------
    | This controller is used in case of request failure like [404 not found]
    | Only in case of ApiMode = true
    | -----------------------------------------------------------
    */

    Config::API__HTTP_ERROR_CONTROLLER => "HttpError",



    /*
    | ----------------------------------------------------------
    | Api Sub Directory (If ApiMode = false)
    | -----------------------------------------------------------
    */

    Config::API__CONTROLLERS_DIR => "Api"


];
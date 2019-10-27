<?php

use Semiorbit\Config\Config;

return [


    /*
    | -----------------------------------------------------------
    | AUTHENTICATION & USERS TABLE AND FIELDS
    | -----------------------------------------------------------
    */

    Config::AUTH__USERS_MODEL => "App\\Users",


    Config::AUTH__USERS_MODEL_FIELDS => [

        'Identity' => 'Email',

        'Password' => 'Password',

        'Role' => 'UserRole'],


    Config::AUTH__LOGIN_IDENTITY_INPUT => 'user_name',

    Config::AUTH__LOGIN_PASSWORD_INPUT => 'user_password',

    Config::AUTH__SUPER_ADMIN_ROLE_ID => 'A'

];
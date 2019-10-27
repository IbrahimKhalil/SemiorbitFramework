<?php

use Semiorbit\Config\Config;

return [

    /*
    | -----------------------------------------------------------
	| DATABASE CONNECTIONS
    | -----------------------------------------------------------
    */

	Config::DB__CONNECTIONS => [

        'mysql' => [

            'driver' => 'mysqli',

            'host' => env('DB_HOST', 'localhost'),

            'user' => env('DB_USER', 'root'),

            'password' => env('DB_PASSWORD'),

            'db' => env('DB_DATABASE')
        ]

    ]


];
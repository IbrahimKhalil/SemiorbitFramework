<?php

use Semiorbit\Config\Config;
use Semiorbit\Http\Request;

return [

    /*
    | -----------------------------------------------------------
    | ACTIONS
    | -----------------------------------------------------------
    |
    |  GENERAL NOTES: ______________________________________________________________
    |
    |  1. ONLY controller 'Public' non-static methods will run as actions!
    |  	  ['Static', 'Private' and 'Protected'] methods will never be run as actions.
    |  2. A method that name starts with '_'  will never be run as an action too.
    |  ______________________________________________________________________________
    |
    |  [defined actions] ' Recommended for security and usability purposes.
    |  KEY is the action alias that will be used in the request uri, so take care!
    |
    |  Example:
    |  http://my-domain.com/en/blog/edit/2
    |  According to default $Actions array, this will run 'Edit' method with parameter id = 2 in the 'blog' controller.
    |
    |  NB.
    |  Although controller and action segments are case-insensitive, it is preferable to use param-case. For example:
    |  Alias for method 'ShowProfile' (or 'showProfile') will be 'show-profile'. Anyway 'ShowProfile' could be used too.
    |  http://my-domain.com/en/users/show-profile/5
    |
    |  Key   =>  Action name or alias (eg. 'edit' or 'show-profile'). Param case should be used for aliases/keys.
    |  Value => [String or Array]
    | 		  =>    String : ('Edit') will run exactly the method that has this name with default settings.
    |		  =>  Array:
    |		  =>    'method' if not set the key will be the method by default.
    |		  =>    'view'   view extension eg. '[edit].form.phtml'
    |		  =>    'pms'    parameters pattern used to parse PATH_INFO starts after 'http://www.my-domain.com/lang/controller/action/'
    |		  =>    'allow'  access permissions.
    |         =>    'box'    scaffolding default box.
    |         =>    'cp'     scaffolding show/hide control panel.
    */

    Config::ACTIONS => [


        'index'  => [ 	'method'=>'Index'                                                                    ],

        'view' 	 => [ 	'method'=>'Show',	    'view'=>'card',     'pms' => ':id'                           ],

        'edit' 	 => [ 	'method'=>'Edit',	    'view'=>'form',     'pms' => ':id', 	'allow'=>SUPER_ADMIN ],

        'create' => [ 	'method'=>'Edit',	    'view'=>'form', 	                    'allow'=>SUPER_ADMIN ],

        'delete' => [ 	'method'=>'Delete',	    'view'=>'delete',   'pms' => ':id', 	'allow'=>SUPER_ADMIN ],

        'table'  => [ 	'method'=>'TableView',  'view' => 'table'                                            ],

        'list' 	 => [ 	'method'=>'ListView',	'view'=>'list',		'pms' => ':filter'                       ],

    ],


    Config::REST_ACTIONS => [


        'index' =>   ['method' => 'Index',   'verb' => Request::VERB_GET],

        'view' =>     ['method' => 'Show',    'verb' => Request::VERB_GET, 'view' => 'card',  'pms' => ':id'],

        'update' =>   ['method' => 'Update',  'verb' => Request::VERB_PUT, 'view' => 'update', 'pms' => ':id', 'allow' => SUPER_ADMIN],

        'store' =>    ['method' => 'Store',   'verb' => Request::VERB_POST, 'view' => 'form',   'pms' => ':id', 'allow' => SUPER_ADMIN],

        'create' =>   ['method' => 'Store',   'verb' => Request::VERB_POST, 'view' => 'form',                     'allow' => SUPER_ADMIN],

        'delete' =>   ['method' => 'Delete',  'verb' => Request::VERB_DELETE, 'view' => 'delete', 'pms' => ':id', 'allow' => SUPER_ADMIN]

    ],


    /*
    | 'AUTO' => Detect 'Actions Aliases' in $this->Actions >>
    |  then 'Allowed Public Methods' (by Name).
    |	'EXPLICIT' =>  Restrict to $this->Actions explicitly defined array only.
    */

    Config::ACTIONS__MODE => 'AUTO',

    /*
    | A list of controller public methods that are not allowed to run as actions.
    | Example ( public function CalculateSum() {} )
    | static $ActionsDenied = ['calculate-sum'];
    */

    Config::ACTIONS__DENIED => [],

    /*
    | Index Action
    */

    Config::ACTIONS__INDEX => 'index',


    /*
    | List of Default Actions By Verb
    | Each item should follow this pattern
    | [verb, action alias, number of parameters (0 for unlimited)]
    */

    Config::ACTIONS__DEFAULT_BY_VERB => [

        [Request::VERB_GET, 'index', 0],

        [Request::VERB_GET, 'view', 1]

    ],


    /*
    | List of Default Actions By Verb [For RestControllers]
    | Each item should follow this pattern
    | [verb, action alias, number of parameters (0 for unlimited)]
    */

    Config::ACTIONS__DEFAULT_REST_BY_VERB => [

        [Request::VERB_GET, 'index', 0],

        [Request::VERB_GET, 'view', 1],

        [Request::VERB_POST, 'create', 0],

        [Request::VERB_POST, 'store', 1],

        [Request::VERB_PUT, 'update', 1],

        [Request::VERB_DELETE, 'delete', 1]

    ]

];
 
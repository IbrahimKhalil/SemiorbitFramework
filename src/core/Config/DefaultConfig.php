<?php
/*
*------------------------------------------------------------------------------------------------
* SEMIORBIT - DEFAULT CONFIGURATIONS				 					 semiorbit.com
*------------------------------------------------------------------------------------------------
* 
* Please copy this file to 'BASEPATH/config/Config.php' then rename class name to 'Config'
* 
*/

namespace Semiorbit\Config;




class DefaultConfig
{

/* 
 *---------------------------------------------------------------
 * SKELETON & PATHS
 *---------------------------------------------------------------
 */

	## -----------------------------------------------------------
	## SEMIORBIT Framework Folder PathInfo
	## -----------------------------------------------------------

	static $FW = "../semiorbit/";
	
	## -----------------------------------------------------------
	## APPLICATION Folder PathInfo
	## -----------------------------------------------------------

	static $AppPath = "";

	
	
	## NOT REQUIRED // if = "" then $_SERVER['SCRIPT_NAME'] will be used
	## eg. /myProject/index.php
	static $IndexFilePath = "";


    ## -----------------------------------------------------------
    ## Environment
    ## -----------------------------------------------------------

    static $Environment = CFG::ENV_PRODUCTION;

    static $DebugMode = false;

    ## -----------------------------------------------------------
    ## LANG - PLEASE LIMIT THIS ARRAY TO YOUR AVAILABLE LANGs
    ##
    ## static $lang = array('en', 'de', 'ar', 'en_US'=>'us', 'en_GB'=>'uk');
    ## -----------------------------------------------------------
	
	static $Lang = array('en');
	
	static $DefaultLang = 'en';
	

	## PathInfo ##
	static $LangPath = "src/lang";
	
	static $LangPathExt = ".inc";
	## -----------------------------------------------------------
	## DATABASE CONNECTIONS 
	## -----------------------------------------------------------

	static $Connections = array(
	
		'mysql'=>array('driver'=>'mysql', 'host'=>'localhost', 'user'=>'root', 'password'=>'', 'db'=>'')
	
	);
	
	## -----------------------------------------------------------
	## Main Controller - Home Page: is where to go  
	## by default the FW will look for (in order):
	## ('index', 'home', 'main'), if not found then
	## FW/controllers/main.class.php will be used as
	## Main Controller. This in its turn will look for
	## TPL/[index, home, main].[tpl_ext] as a home page view
	## If not found it will Render FW/views/main.tpl.inc as home.
	## -----------------------------------------------------------

	static $MainPage = "Main";
	
	## -----------------------------------------------------------
	## URL Controller
	## -----------------------------------------------------------
	
	static $URLController = "URLController";
	
	## -----------------------------------------------------------
	## App Class
	## -----------------------------------------------------------
	
	static $AppClass = "App";
	
	## -----------------------------------------------------------
	## Models 
	## -----------------------------------------------------------

	// PathInfo
	static $Models = "";
	
	// File Extension 
	static $ModelsExt = ".php";

	## -----------------------------------------------------------
	## CONTROLLERS
	## -----------------------------------------------------------

	## PathInfo ##
	static $Controllers = "Http";
	
	## ClassName_Suffix ##
	static $ControllerSuffix = "Ctrl";
	
	## File Extension ##
	static $ControllersExt = ".php";

    ## -----------------------------------------------------------
    ## Api
    ## -----------------------------------------------------------

    ## Api Sub Directory ##
    static $ApiControllers = "Api";

    ## Is Project an Api? ##

    # True: all controllers should be restful controllers, and no sub directory/ version directory is needed #
    # False: Api controllers should be located in
    #        Http/{$ApiControllers} sub directory. Version sub directory is needed in this case #

    static $ApiMode = false;


    # This controller is used in case of request failure like [404 not found] #
    # Only in case of ApiMode = true #

    static $HttpErrorController = "HttpError";
	
	## -----------------------------------------------------------
    ## VIEWS 
    ## -----------------------------------------------------------

	## PathInfo to template ##
	
	static $Views = "src/views";
	
	## File Extension ##
	
	static $ViewsExt = ".phtml";
	
	## Container File Name ##
	
	static $DefaultLayout = "layout";

	## Page Title Separator::  My Project - My Page ##

	static $PageTitleSeparator = " - ";

	## WIDGETS ##
	
	static $WidgetExt = ".widget.phtml";

    ## BOX ##

    static $BoxExt = ".box.phtml";
	
	## PathInfo to theme in public folder ##
	
	static $Theme = "theme";
	
	## -----------------------------------------------------------
	## HTML/JS EXTERNAL PLUGINS OR THIRD PARTY/VENDOR TOOLS
	## -----------------------------------------------------------
	
	## PathInfo to ext resources in public folder ##
	
	static $Ext = "ext";
	
	## -----------------------------------------------------------
	## UPLOAD DOCUMENTS & IMAGES FOLDER SETTINGS
	## -----------------------------------------------------------

	# Relative path to upload directory in PUBLICPATH root
	# or absolute path to upload directory anywhere else.

	static $DocumentsPath = "documents";

    # Leave empty to auto generate url to documents folder
    # in PUBLIC directory. But if "documents path" is not relative to
    # PUBLIC directory this option should be explicitly defined.

    static $DocumentsURL = "";
	
	## -----------------------------------------------------------
    ## ACTIONS 
    ## -----------------------------------------------------------
	
	## GENERAL NOTES: ###############################################################
	## 1. ONLY controller 'Public' non-static methods will run as actions!
	## 	  ['Static', 'Private' and 'Protected'] methods will never be run as actions.
	## 2. A method that name starts with '_'  will never be run as an action too.
	#################################################################################
	
	##  [defined actions] ' Recommended for security and usability purposes.
	##  KEY is the action alias that will be used in the request uri, so take care!

    ##  Example:
	##  http://my-domain.com/en/blog/edit/2
	##  According to default $Actions array, this will run 'Edit' method with parameter id = 2 in the 'blog' controller.

    ##  NB.
    ##  Although controller and action segments are case-insensitive, it is preferable to use param-case. For example:
    ##  Alias for method 'ShowProfile' (or 'showProfile') will be 'show-profile'. Anyway 'ShowProfile' could be used too.
    ##  http://my-domain.com/en/users/show-profile/5
	
	##  Key   =>  Action name or alias (eg. 'edit' or 'show-profile'). Param case should be used for aliases/keys.
	##  Value => [String or Array]
	## 		  =>    String : ('Edit') will run exactly the method that has this name with default settings.
	##		  =>  Array:
	##		  =>    'method' if not set the key will be the method by default.
	##		  =>    'view'   view extension eg. '[edit].form.phtml'
	##		  =>    'pms'    parameters pattern used to parse PATH_INFO starts after 'http://www.my-domain.com/lang/controller/action/'
	##		  =>    'allow'  access permissions.
	##        =>    'box'    scaffolding default box.
	##        =>    'cp'     scaffolding show/hide control panel.

	
	static $Actions = array(
        
	    
        'index'  => array( 	'method'=>'Index' ),
        
        'view' 	 => array( 	'method'=>'Show',	    'view'=>'card',     'pms' => ':id' ),

        'edit' 	 => array( 	'method'=>'Edit',	    'view'=>'form',     'pms' => ':id', 	'allow'=>SUPER_ADMIN ),

        'create' => array( 	'method'=>'Edit',	    'view'=>'form', 	                    'allow'=>SUPER_ADMIN ),

        'delete' => array( 	'method'=>'Delete',	    'view'=>'delete',   'pms' => ':id', 	'allow'=>SUPER_ADMIN ),
        
        'table'  => array( 	'method'=>'TableView',  'view' => 'table' ),

        'list' 	 => array( 	'method'=>'ListView',	'view'=>'list',		'pms' => ':filter' ),
        
	);
	
	 ## 'AUTO' => Detect 'Actions Aliases' in $this->Actions >>
	            // then 'Allowed Public Methods' (by Name).
	 ##	'EXPLICIT' =>  Restrict to $this->Actions explicitly defined array only.

	static $ActionsMode = 'AUTO';
	
	## A list of controller public methods that are not allowed to run as actions.
	## Example ( public function CalculateSum() {} )
	## static $ActionsDenied = array('calculate-sum');
	
	static $ActionsDenied = array();
	
	## Index Action
	
	static $IndexAction = 'index';
	
	## -----------------------------------------------------------
    ## CUSTOMIZE BASIC URI PARAMETERS NAMING  
    ## -----------------------------------------------------------
	
	static $BasicParamCustomNaming = array('lang'=>"lang",'class'=>"class",'action'=>"action",'id'=>"id",'layout'=>'layout');
	
	## -----------------------------------------------------------
	## ERRORS
	## -----------------------------------------------------------
	
	static $ShowErrReport = false;
	
	## -----------------------------------------------------------
	## DEFAULT PAGINATION WIDGET
	## -----------------------------------------------------------
	
	static $PaginationWidget = "pagination";

    static $PageParam = "page";

	static $RowsPerPage = 6;
	

	## -----------------------------------------------------------
	## MINIFY HTML OUTPUT
	## -----------------------------------------------------------
	
	static $SanitizeOutput = false;
	
	## -----------------------------------------------------------
	## AUTHENTICATION & USERS TABLE AND FIELDS
	## -----------------------------------------------------------
	
	static $UsersModel = "App\\Users";
	
	static $UsersModelFields = array('Identity'=>'Email', 'Password' => 'Password', 'Role' => 'UserRole');

	static $LoginIdentityInput = 'user_name';

	static $LoginPasswordInput = 'user_password';

	static $PasswordHash = 'md5';

    static $SuperAdminRoleId = 'A';

	
	## -----------------------------------------------------------
	## CLASS ALIAS LIST
	## -----------------------------------------------------------
	
	static $ClassAlias = [

	    ## Example

		## 'Semiorbit\\Field\\DataType' =>  array('DataType')
	];
	
	# -----------------------------------------------------------
	## APP CLASSES
	## -----------------------------------------------------------
	
	## APP NAMESPACE
	static $AppNamespace = "App";
	
	## File Extension
	static $AppClassExt = ".php";
	
	## AUTOLOAD GLOBAL CLASSES IN THESE DIRECTORIES 
	## (Autoload classes are loaded in global namespace - no namespace should be used)
	
	static $AutoloadClasses = array( "libraries" => array( 'ext' => ".lib.inc", 'dir' => "app/libraries" ) );
		
	
	## -----------------------------------------------------------
	## DEFAULT FORM TEMPLATE
	## -----------------------------------------------------------
	
	static $DefaultFormTemplate = "form";
	
	## -----------------------------------------------------------
	## DEFAULT FORM HONEYPOTS
	## -----------------------------------------------------------
	
	static $HoneypotsMax = 3;
	
	static $HoneypotsLabels = array();
	
	## -----------------------------------------------------------
	## DEFAULT FORM INPUT [[NAME FORMAT]] 
	## -----------------------------------------------------------
	
	## Input name Format string can be used to prefix or customize
	## html input "name" or "id" [/id/ if not explicitly provided 
	## in field props].
	
	## Default Format = ":model_:name"
	
	## Keywords
	## --------
	## :model = Model/DataSet class name __CLASS__
	## :name  = Field name 
	
	## EG. Html output for name Format ":model_:name"
	## will be as follows:
	
	## <input type="text" name="User_FirstName" />
	## User = model name, FirstName = field name
	
	## Cautions
	## --------
	
	## A. Format string should follow the pattern "/^[A-Za-z0-9_]+$/"
	## in addition to keywords :model and :name. Otherwise the system
	## will revert back to ":model_:name" Format.

	## B. If keyword :name is not included in Format string, it will be
	## added to the end of the Format string anyway. so if the Format
	## string assigned as empty string that means ":name" Format will
	## be used eventually.
	
	static $FormInputNameFormat = ":model_:name";
	
	
}

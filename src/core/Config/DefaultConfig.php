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




use Semiorbit\Base\Application;
use Semiorbit\Http\Actions;

abstract class DefaultConfig
{

    #region "Config Groups"

    const GROUP__ACTIONS = "actions";

    const GROUP__API = "api";

    const GROUP__APP = "app";

    const GROUP__AUTH = "auth";

    const GROUP__CONTROLLERS = "controllers";

    const GROUP__DB = "db";

    const GROUP__FORMS = "forms";

    const GROUP__FRAMEWORK = "framework";

    const GROUP__LANG = "lang";

    const GROUP__MODELS = "models";

    const GROUP__SCAFFOLDING = "scaffolding";

    const GROUP__VIEWS = "views";

    const GROUP_SERVICES = "services";

    #endregion


    #region "DB"

	## -----------------------------------------------------------
	## DATABASE CONNECTIONS 
	## -----------------------------------------------------------

    const DB__CONNECTIONS = "db_connections";

    /**
     * @return array
     */

    public static function DbConnections()
    {
        return static::ValueOf(self::GROUP__DB, self::DB__CONNECTIONS);
    }

    #endregion


    #region "APP"

    ## -----------------------------------------------------------
    ## Environment
    ## -----------------------------------------------------------

    const FWK_ENV_LIST = 'SEMIORBIT_FWK_ENV_LIST';

    const ENV_DEVELOPMENT = 'dev';

    const ENV_PRODUCTION = 'production';
    
    
    const APP__ENVIRONMENT = "environment";

    public static function Environment()
    {
        return static::ValueOf(self::GROUP__APP, self::APP__ENVIRONMENT);
    }



    const APP__DEBUG_MODE = "debug_mode";

    public static function DebugMode()
    {
        return static::ValueOf(self::GROUP__APP, self::APP__DEBUG_MODE);
    }



    ## -----------------------------------------------------------
    ## Languages
    ## -----------------------------------------------------------

    const APP__LANGUAGES = "languages";

    /**
     * @param bool $default
     * @return array
     */
    public static function Languages()
    {

        $languages =  (array) static::ValueOf(self::GROUP__APP, self::APP__LANGUAGES);

        if (empty($languages))

            Application::Abort(503, "Undefined language config");

        return $languages;

    }



    ## -----------------------------------------------------------
    ## Index file
    ## -----------------------------------------------------------

    ## NOT REQUIRED // if = "" then $_SERVER['SCRIPT_NAME'] will be used
    ## eg. /myProject/index.php

    const APP_INDEX_FILE_PATH = "index_file_path";

    public static function IndexFilePath()
    {
        return static::ValueOf(self::GROUP__APP, self::APP_INDEX_FILE_PATH);
    }



    # -----------------------------------------------------------
    ## APP NAMESPACE
    ## -----------------------------------------------------------

    const APP__NAMESPACE = "app_namespace";

    public static function AppNamespace()
    {
        return static::ValueOf(self::GROUP__APP, self::APP__NAMESPACE);
    }


    ## -----------------------------------------------------------
    ## App Class
    ## -----------------------------------------------------------

    const APP__CLASS = "app_class";

    public static function AppClass()
    {
        return static::ValueOf(self::GROUP__APP, self::APP__CLASS);
    }


    ## -----------------------------------------------------------
    ## Main Controller
    ##
    ## Home Page: is where to go
    ## by default the FW will look for (in order):
    ## ('index', 'home', 'main'), if not found then
    ## FW/controllers/main.class.php will be used as
    ## Main Controller. This in its turn will look for
    ## TPL/[index, home, main].[tpl_ext] as a home page view
    ## If not found it will Render FW/views/main.tpl.inc as home.
    ## -----------------------------------------------------------

    const APP__MAIN_PAGE = "main_page";


    ## -----------------------------------------------------------
    ## MINIFY HTML OUTPUT
    ## -----------------------------------------------------------

    const APP__SANITIZE_OUTPUT = "sanitize_output";

    public static function SanitizeOutput()
    {
        return static::ValueOf(self::GROUP__APP, self::APP__SANITIZE_OUTPUT);
    }


    ## -----------------------------------------------------------
    ## HTML/JS EXTERNAL PLUGINS OR THIRD PARTY/VENDOR TOOLS
    ## -----------------------------------------------------------

    ## PathInfo to ext resources in public folder ##

    const APP__JS_EXT_DIR = "js_ext_dir";

    public static function JsExtDir()
    {
        return static::ValueOf(self::GROUP__APP, self::APP__JS_EXT_DIR);
    }


    ## -----------------------------------------------------------
    ## CLASS ALIAS LIST
    ## -----------------------------------------------------------

    const APP__CLASS_ALIAS = "class_alias";

    public static function ClassAlias()
    {
        return (array) static::ValueOf(self::GROUP__APP, self::APP__CLASS_ALIAS);
    }


    #endregion


    #region "SCAFFOLDING"

    ## -----------------------------------------------------------
    ## DEFAULT PAGINATION WIDGET
    ## -----------------------------------------------------------



    const SCAFFOLDING__PAGINATION_WIDGET = "pagination_widget";

    public static function PaginationWidget()
    {
        return static::ValueOf(self::GROUP__SCAFFOLDING, self::SCAFFOLDING__PAGINATION_WIDGET);
    }



    const SCAFFOLDING__PAGE_PARAM = "page_param";

    public static function PageParam()
    {
        return static::ValueOf(self::GROUP__SCAFFOLDING, self::SCAFFOLDING__PAGE_PARAM);
    }



    const SCAFFOLDING__ROWS_PER_PAGE = "rows_per_page";

    public static function RowsPerPage()
    {
        return static::ValueOf(self::GROUP__SCAFFOLDING, self::SCAFFOLDING__ROWS_PER_PAGE);
    }



    ## -----------------------------------------------------------
    ## ERRORS
    ## -----------------------------------------------------------



    const SCAFFOLDING__SHOW_ERR_REPORT = "show_err_report";

    public static function ShowErrReport()
    {
        return static::ValueOf(self::GROUP__SCAFFOLDING, self::SCAFFOLDING__SHOW_ERR_REPORT);
    }

    #endregion


    #region "API"


    ## -----------------------------------------------------------
    ## Api
    ## -----------------------------------------------------------



    ## Is Project an Api? ##

    # True: all controllers should be restful controllers, and no sub directory/ version directory is needed #
    # False: Api controllers should be located in
    #        Http/{$ApiControllers} sub directory. Version sub directory is needed in this case #

   const API__MODE = 'mode';

    public static function ApiMode()
    {
        return static::ValueOf(self::GROUP__API, self::API__MODE);
    }



    # This controller is used in case of request failure like [404 not found] #
    # Only in case of ApiMode = true #

    const API__HTTP_ERROR_CONTROLLER = "http_error_controller";



    ## Api Sub Directory ##
    const API__CONTROLLERS_DIR = "api_controllers_dir";

    public static function ApiControllersDir()
    {
        return static::ValueOf(self::GROUP__API, self::API__CONTROLLERS_DIR);
    }


    #endregion


    #region "CONTROLLERS"


    ## -----------------------------------------------------------
    ## CONTROLLERS
    ## -----------------------------------------------------------



    ## Path ##
    const CONTROLLERS_DIR = "dir";
   
    public static function ControllersDir()
    {
        return static::ValueOf(self::GROUP__CONTROLLERS, self::CONTROLLERS_DIR);
    }



    ## ClassName_Suffix ##
    const CONTROLLERS_SUFFIX = "suffix";

    public static function ControllerSuffix()
    {
        return static::ValueOf(self::GROUP__CONTROLLERS, self::CONTROLLERS_SUFFIX);
    }



    ## File Extension ##
    const CONTROLLERS_EXT = "ext";

    public static function ControllersExt()
    {
        return static::ValueOf(self::GROUP__CONTROLLERS, self::CONTROLLERS_EXT);
    }



    ## -----------------------------------------------------------
    ## CUSTOMIZE BASIC URI PARAMETERS NAMING
    ## -----------------------------------------------------------

    const CONTROLLERS__BASIC_PARAM_CUSTOM_NAMING = "basic_param_custom_naming";

    public static function BasicParamCustomNaming($param_key = null)
    {

        $values = (array) static::ValueOf(self::GROUP__CONTROLLERS, self::CONTROLLERS__BASIC_PARAM_CUSTOM_NAMING);

        if ($param_key)

            return $values[$param_key] ?? null;

        return $values;

    }

    #endregion


    #region "MODELS"


    ## -----------------------------------------------------------
    ## Models 
    ## -----------------------------------------------------------



    const MODELS__DIR = "dir";

    public static function ModelsDir()
    {
        return static::ValueOf(self::GROUP__MODELS, self::MODELS__DIR);
    }


     
    const MODELS__EXT = "ext";
    
    public static function ModelsExt()
    {
        return static::ValueOf(self::GROUP__MODELS, self::MODELS__EXT);
    }



    ## -----------------------------------------------------------
    ## UPLOAD DOCUMENTS & IMAGES FOLDER SETTINGS
    ## -----------------------------------------------------------



    # Relative path to upload directory in PUBLICPATH root
    # or absolute path to upload directory anywhere else.

    const MODELS__DOCUMENTS_PATH = "documents_path";



    # Leave empty to auto generate url to documents folder
    # in PUBLIC directory. But if "documents path" is not relative to
    # PUBLIC directory this option should be explicitly defined.

    const MODELS__DOCUMENTS_URL = "documents_url";

    #endregion


    #region "LANG"

    /*
    | ------------------------------------------------
    | LANG
    | ------------------------------------------------
     */

    ## Lang Dir ##
    
    const LANG__DIR = "dir";

    public static function LangDir()
    {
        return static::ValueOf(self::GROUP__LANG, self::LANG__DIR);
    }
    
    
    const LANG__EXT = "ext";

    public static function LangExt()
    {
        return static::ValueOf(self::GROUP__LANG, self::LANG__EXT);
    }

    #endregion


    #region "VIEWS"


    ## -----------------------------------------------------------
    ## VIEWS__DIR
    ## -----------------------------------------------------------

    ## Path to template ##

    const VIEWS__DIR = "dir";

    public static function ViewsDir()
    {
        return static::ValueOf(self::GROUP__VIEWS, self::VIEWS__DIR);
    }



    ## File Extension ##

    const VIEWS__EXT = "ext";

    public static function ViewsExt()
    {
        return static::ValueOf(self::GROUP__VIEWS, self::VIEWS__EXT);
    }

    ## Container File FullName ##

    const VIEWS__DEFAULT_LAYOUT = "default_layout";

    public static function DefaultLayout()
    {
        return static::ValueOf(self::GROUP__VIEWS, self::VIEWS__DEFAULT_LAYOUT);
    }



    ## Page Title Separator::  My Project - My Page ##

    const VIEWS__PAGE_TITLE_SEPARATOR = "page_title_separator";

    public static function PageTitleSeparator()
    {
        return static::ValueOf(self::GROUP__VIEWS, self::VIEWS__PAGE_TITLE_SEPARATOR);
    }



    ## WIDGETS ##

    const VIEWS__WIDGET_EXT = "widget_ext";

    public static function WidgetExt()
    {
        return static::ValueOf(self::GROUP__VIEWS, self::VIEWS__WIDGET_EXT);
    }


    ## BOX ##

    const VIEWS__BOX_EXT = "box_ext";

    public static function BoxExt()
    {
        return static::ValueOf(self::GROUP__VIEWS, self::VIEWS__BOX_EXT);
    }



    ## PathInfo to theme in public folder ##

    const VIEWS__THEME = "theme";

    public static function Theme()
    {
        return static::ValueOf(self::GROUP__VIEWS, self::VIEWS__THEME);
    }


    #endregion


    #region "ACTIONS"

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


    const ACTIONS = "actions";

    public static function Actions()
    {
        return Actions::PrepareActions(

            static::ValueOf(self::GROUP__ACTIONS, self::ACTIONS)

        );
    }


    const REST_ACTIONS = "rest_actions";

    public static function RestActions()
    {
        return Actions::PrepareActions(

            static::ValueOf(self::GROUP__ACTIONS, self::REST_ACTIONS)

        );
    }



    ## 'AUTO' => Detect 'Actions Aliases' in $this->Actions >>
    // then 'Allowed Public Methods' (by FullName).
    ##	'EXPLICIT' =>  Restrict to $this->Actions explicitly defined array only.

    const ACTIONS__MODE = 'mode';

    public static function ActionsMode()
    {
        return static::ValueOf(self::GROUP__ACTIONS, self::ACTIONS__MODE);
    }



    ## A list of controller public methods that are not allowed to run as actions.
    ## Example ( public function CalculateSum() {} )
    ## static $ActionsDenied = array('calculate-sum');

    const ACTIONS__DENIED = "denied";

    public static function ActionsDenied()
    {
        return static::ValueOf(self::GROUP__ACTIONS, self::ACTIONS__DENIED);
    }



    ## Index Action

    const ACTIONS__INDEX = 'index_action';

    public static function IndexAction()
    {
        return static::ValueOf(self::GROUP__ACTIONS, self::ACTIONS__INDEX);
    }



    const ACTIONS__DEFAULT_BY_VERB = 'def_action_by_verb';

    public static function DefaultActionByVerb()
    {
        return static::ValueOf(self::GROUP__ACTIONS, self::ACTIONS__DEFAULT_BY_VERB);
    }


    const ACTIONS__DEFAULT_REST_BY_VERB = 'def_rest_action_by_verb';

    public static function DefaultRestActionByVerb()
    {
        return static::ValueOf(self::GROUP__ACTIONS, self::ACTIONS__DEFAULT_REST_BY_VERB);
    }


    #endregion


    #region "AUTH"


    ## -----------------------------------------------------------
    ## AUTHENTICATION & USERS TABLE AND FIELDS
    ## -----------------------------------------------------------



    const AUTH__USERS_MODEL = "users_model";

    public static function UsersModel()
    {
        return static::ValueOf(self::GROUP__AUTH, self::AUTH__USERS_MODEL);
    }



    const AUTH__USERS_MODEL_FIELDS = "users_model_fields";

    public static function UsersModelFields($field_key = null)
    {

        $values = (array) static::ValueOf(self::GROUP__AUTH, self::AUTH__USERS_MODEL_FIELDS);

        if ($field_key)

            return $values[$field_key] ?? null;

        return $values;

    }



    const AUTH__LOGIN_IDENTITY_INPUT = 'login_identity_input';

    public static function LoginIdentityInput()
    {
        return static::ValueOf(self::GROUP__AUTH, self::AUTH__LOGIN_IDENTITY_INPUT);
    }




    const AUTH__LOGIN_PASSWORD_INPUT = 'login_password_input';

    public static function LoginPasswordInput()
    {
        return static::ValueOf(self::GROUP__AUTH, self::AUTH__LOGIN_PASSWORD_INPUT);
    }


    const AUTH__SUPER_ADMIN_ROLE_ID = 'super_admin_role_id';

    public static function SuperAdminRoleId()
    {
        return static::ValueOf(self::GROUP__AUTH, self::AUTH__SUPER_ADMIN_ROLE_ID);
    }

    #endregion


    #region "FORMS"

	
	## -----------------------------------------------------------
	## DEFAULT FORM TEMPLATE
	## -----------------------------------------------------------



	const FORMS__DEFAULT_FORM_TEMPLATE = "default_form_template";

    public static function DefaultFormTemplate()
    {
        return static::ValueOf(self::GROUP__FORMS, self::FORMS__DEFAULT_FORM_TEMPLATE);
    }


	## -----------------------------------------------------------
	## DEFAULT FORM HONEYPOTS
	## -----------------------------------------------------------



	const FORMS__HONEYPOTS_MAX = "honeypots_max";

    public static function HoneypotsMax()
    {
        return static::ValueOf(self::GROUP__FORMS, self::FORMS__HONEYPOTS_MAX);
    }



	const FORMS__HONEYPOTS_LABELS = "honeypots_labels";

	public static function HoneypotsLabels()
    {
        return static::ValueOf(self::GROUP__FORMS, self::FORMS__HONEYPOTS_LABELS);
    }



	/*
	| -----------------------------------------------------------
	| DEFAULT FORM INPUT [[NAME FORMAT]]
	| -----------------------------------------------------------
	|
	|  Input name Format string can be used to prefix or customize
	|  html input "name" or "id" [/id/ if not explicitly provided
	|  in field props].
	|
	|  Default Format = ":model_:name"
	|
	|  Keywords
	|  --------
	|  :model = Model/DataSet class name __CLASS__
	|  :name  = Field name
	|
	|  EG. Html output for name Format ":model_:name"
	|  will be as follows:
	|
	|  <input type="text" name="User_FirstName" />
	|  User = model name, FirstName = field name
	|
	|  Cautions
	|  --------
	|
	|  A. Format string should follow the pattern "/^[A-Za-z0-9_]+$/"
	|  in addition to keywords :model and :name. Otherwise the system
	|  will revert back to ":model_:name" Format.
	|
	|  B. If keyword :name is not included in Format string, it will be
	|  added to the end of the Format string anyway. so if the Format
	|  string assigned as empty string that means ":name" Format will
	|  be used eventually.
	|
	*/


	
	const FORMS__INPUT_NAME_FORMAT = "input_name_format";

	public static function FormInputNameFormat()
    {
        return static::ValueOf(self::GROUP__FORMS, static::FORMS__INPUT_NAME_FORMAT);
    }

    #endregion


    abstract public static function ValueOf($config_group, $config_key, $default = false);

	abstract public static function setValueOf($config_group, $config_key, $value);
	
}

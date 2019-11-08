<?php
/* 
 *------------------------------------------------------------------------------------------------
 * SEMIORBIT - CONTROLLER							 					 semiorbit.com
 *------------------------------------------------------------------------------------------------
 */

namespace Semiorbit\Http;



use Semiorbit\Base\Application;
use Semiorbit\Component\Services;
use Semiorbit\Output\View;
use Semiorbit\Support\Path;
use Semiorbit\Support\Str;
use Semiorbit\Support\ClipboardTrait;
use Semiorbit\Config\Config;
use Semiorbit\Data\DataSet;
use Semiorbit\Translation\Lang;
use Semiorbit\Component\Finder;


/**
 * Controller Class
 * Controller class is a super class that all controllers in the application should extend
 *
 * @author       semiorbit.com
 * @property DataSet $DataSet
 * @property Request $Request
 * @property View $View
 * @property Actions $Actions
 * @property ScaffoldBase $Scaffolding
 * @property Response $Response
 * @property array $Params
 */
abstract class Controller
{

    public $DataSet;

    public $Request;

    public $View;

    public $Actions;

    public $Response;

    public $Scaffolding;

    public $Params;

    public $Package;

    public $PackagePrefix;



    public $ControllerName = '';

    public $ControllerPath = '';

    public $ControllerTitle = '';

    public $ModelPath = '';


    const DataSet = null;


    use ClipboardTrait {
        Clipboard as protected;
    }


    function __construct(Request $request = null)
    {

        $this->Actions = new Actions($this, []);

        $this->Actions->setDefaultByVerb('index', Request::VERB_GET);


        $this->Request = $request;

        $this->Params = &$this->Request->Params;


        $this->Package = static::Package();

        $this->PackagePrefix = ($pkg = $this->Package) ? $pkg . '::' : '';



        $this->View = new View();

        $this->View->UseRequest( $this->Request );

        // PREPARE DEFAULT DATASET [MODEL] //////////////////////////////

        $this->ControllerName = static::Name();

        $this->ControllerTitle = static::IndexTitle();

        $this->ControllerPath = Url::BaseUrl() . $this->Request->Lang . "/" . Str::ParamCase( $this->ControllerName ) . "/";


        if ( static::DataSet ) {

            $dataset_name = static::DataSet;

            $this->DataSet = new $dataset_name();

            $this->DataSet->UseController($this);

        } else {

            if ($this->ModelPath = Finder::LookForModel($this->ControllerName, $this->Package)) {

                $this->DataSet = new $this->ModelPath->Class;

                $this->DataSet->UseController($this);

            }

        }

        // RESPONSE

        $this->Response = new Response();


        $this->Initialize();

        $this->onStart();

    }

    protected function Initialize()
    {
        $this->Response->UseView($this->View);
    }


    protected function onStart()
    {
        // This event raised after __construct
    }




    abstract public function Index();




    public static function Name($class_name = null)
    {

        if ( ! $class_name ) $class_name = static::class;

        if ( $name = self::Clipboard( $class_name ) ) return $name;

        $short_name = Path::ClassShortName($class_name);

        $suffix_offset = strrpos($short_name, Config::ControllerSuffix());

        if ( $suffix_offset ) {

            $obj = substr($short_name, 0, $suffix_offset);

        } else {

            $suffix_offset = strrpos($short_name, 'Controller');

            $obj = substr($short_name, 0, $suffix_offset);

        }

        self::Clipboard( $class_name, $obj );

        return $obj;

    }


    public static function Namespace($class_name = null)
    {
        return Path::ClassNamespace($class_name ?: static::class);
    }

    public static function Package($class_name = null)
    {
        return Services::FindPackageByControllerNs(static::Namespace($class_name));
    }

    public static function IndexTitle($class_name = null, $use_cache = true)
    {

        if ( $use_cache && $title = static::Clipboard( "index_title_" . $class_name ) ) return $title;

        $name = static::Name( $class_name );

        return static::Clipboard( "index_title_" . $class_name, trans( (static::Package() ? static::Package() . '::' : '') . Str::ParamCase( $name )  . ".__index" ) );

    }

    public static function IndexUrl($class_name = null, $use_cache = true)
    {

        if ( $use_cache && $title = static::Clipboard( "index_url_" . $class_name ) ) return $title;

        $name = Str::ParamCase( static::Name( $class_name ) );

        return static::Clipboard( "index_url_" . $class_name, Url::BaseUrl() . Lang::ActiveLang() . "/" . $name );

    }

    public static function ListViewUrl($class_name = null, $use_cache = true)
    {

        if ( $use_cache && $title = static::Clipboard( "list_url_" . $class_name ) ) return $title;

        $name = Str::ParamCase( static::Name( $class_name ) );

        return static::Clipboard( "list_url_" . $class_name, Url::BaseUrl() . Lang::ActiveLang() . "/" . $name . "/list" );

    }

    public static function TableViewUrl($class_name = null, $use_cache = true)
    {

        if ( $use_cache && $title = static::Clipboard( "table_url_" . $class_name ) ) return $title;

        $name = Str::ParamCase( static::Name( $class_name ) );

        return static::Clipboard( "table_url_" . $class_name, Url::BaseUrl() . Lang::ActiveLang() . "/" . $name . "/table" );

    }

    public static function EditUrl($id = null, $class_name = null, $use_cache = true)
    {

        if ( $use_cache && $title = static::Clipboard( "edit_url_" . $class_name . $id ) ) return $title;

        $name = Str::ParamCase( static::Name( $class_name ) );

        if (! empty($id)) {

            return static::Clipboard( "edit_url_" . $class_name . $id, Url::BaseUrl() . Lang::ActiveLang() . "/" . $name . '/edit/' . $id );

        } else {

            return static::Clipboard( "create_url_" . $class_name, Url::BaseUrl() . Lang::ActiveLang() . "/" . $name . '/create' );
        }

    }

    public static function DeleteUrl($id, $class_name = null, $use_cache = true)
    {

        if ( $use_cache && $title = static::Clipboard( "delete_url_" . $class_name . $id ) ) return $title;

        $name = Str::ParamCase( static::Name( $class_name ) );

        return static::Clipboard( "delete_url_" . $class_name . $id, Url::BaseUrl() . Lang::ActiveLang() . "/" . $name . '/delete/' . $id );

    }

    public static function ShowUrl($id, $class_name = null, $use_cache = true)
    {

        if ( $use_cache && $title = static::Clipboard( "show_url_" . $class_name . $id ) ) return $title;

        $name = Str::ParamCase( static::Name( $class_name ) );

        return static::Clipboard( "show_url_" . $class_name . $id, Url::BaseUrl() . Lang::ActiveLang() . "/" . $name . '/view/' . $id );

    }


}
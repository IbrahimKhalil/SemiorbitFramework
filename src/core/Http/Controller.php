<?php
/* 
 *------------------------------------------------------------------------------------------------
 * SEMIORBIT - CONTROLLER							 					 semiorbit.com
 *------------------------------------------------------------------------------------------------
 */

namespace Semiorbit\Http;



use Semiorbit\Base\Application;
use Semiorbit\Output\View;
use Semiorbit\Support\Path;
use Semiorbit\Support\Str;
use Semiorbit\Support\ClipboardTrait;
use Semiorbit\Config\CFG;
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
 * @property DefaultScaffold $Scaffolding
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



    public $ControllerName = '';

    public $ControllerPath = '';

    public $ControllerTitle = '';

    public $ModelPath = '';


    use ClipboardTrait {
        Clipboard as protected;
    }


    final function __construct(Request $request = null, DataSet $ds = null)
    {

        $this->Actions = new Actions($this, CFG::Actions());

        $this->Actions->setMode(CFG::$ActionsMode)->Deny(CFG::$ActionsDenied);


        $this->Request = $request;

        $this->Params = &$this->Request->Params;


        $this->View = new View();

        $this->View->UseRequest( $this->Request )->UseDefaultLayout();

        // PREPARE DEFAULT DATASET [MODEL] //////////////////////////////

        $this->ControllerName = static::Name();

        $this->ControllerTitle = static::IndexTitle();

        $this->ControllerPath = Url::BaseUrl() . $this->Request->Lang . "/" . Str::ParamCase( $this->ControllerName ) . "/";

        if ( $ds instanceof DataSet ) {

            $this->DataSet = $ds;

            $this->DataSet->UseController($this);

        } else {

            $this->ModelPath = Finder::LookFor($this->ControllerName, Finder::Models);

            if ($this->ModelPath) {

                if (class_exists($this->ModelPath->Class)) {

                    $this->DataSet = new $this->ModelPath->Class;

                    $this->DataSet->UseController($this);

                }

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

        // SCAFFOLDING

        $this->Scaffolding = new DefaultScaffold($this);

    }


    protected function onStart()
    {
        // This event raised after __construct
    }




    public function Index()
    {
        if ($this->Scaffolding && $this->Scaffolding->IsEnabled('Index')) $this->Scaffolding->Index();

        else Application::Abort(404);
    }

    public function Show()
    {
        if ($this->Scaffolding && $this->Scaffolding->IsEnabled('Show')) $this->Scaffolding->Show();

        else Application::Abort(404);
    }

    public function Edit()
    {
        if ($this->Scaffolding && $this->Scaffolding->IsEnabled('Edit')) $this->Scaffolding->Edit();

        else Application::Abort(404);
    }

    public function Delete()
    {
        if ($this->Scaffolding && $this->Scaffolding->IsEnabled('Delete')) $this->Scaffolding->Delete();

        else Application::Abort(404);
    }

    public function ListView()
    {
        if ($this->Scaffolding && $this->Scaffolding->IsEnabled('ListView')) $this->Scaffolding->ListView();

        else Application::Abort(404);
    }

    public function TableView()
    {
        if ($this->Scaffolding && $this->Scaffolding->IsEnabled('TableView')) $this->Scaffolding->TableView();

        else Application::Abort(404);
    }
    




    public static function Name($class_name = null)
    {

        if ( ! $class_name ) $class_name = static::class;

        if ( $name = self::Clipboard( $class_name ) ) return $name;

        $short_name = Path::ClassShortName($class_name);

        $suffix_offset = strrpos($short_name, CFG::$ControllerSuffix);

        if ( $suffix_offset ) {

            $obj = substr($short_name, 0, $suffix_offset);

        } else {

            $suffix_offset = strrpos($short_name, 'Controller');

            $obj = substr($short_name, 0, $suffix_offset);

        }

        self::Clipboard( $class_name, $obj );

        return $obj;

    }


    public static function IndexTitle($class_name = null, $use_cache = true)
    {

        if ( $use_cache && $title = static::Clipboard( "index_title_" . $class_name ) ) return $title;

        $name = static::Name( $class_name );

        return static::Clipboard( "index_title_" . $class_name, trans( Str::ParamCase( $name )  . ".__index" ) );

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
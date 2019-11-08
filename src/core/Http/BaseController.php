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
abstract class BaseController extends Controller
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



    protected function Initialize()
    {

        $this->Actions->UseArray(Config::Actions());

        $this->Actions->setDefaultByVerb('view', Request::VERB_GET, 1);

        $this->View->UseDefaultLayout();

        $this->Response->UseView($this->View);

        // SCAFFOLDING

        $this->Scaffolding = new ScaffoldBase($this);

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
    




}
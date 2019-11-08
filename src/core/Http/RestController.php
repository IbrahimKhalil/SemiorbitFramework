<?php
/*
 *------------------------------------------------------------------------------------------------
 * SEMIORBIT - RESTFUL CONTROLLER							 			      semiorbit.com
 *------------------------------------------------------------------------------------------------
 */


namespace Semiorbit\Http;
use Semiorbit\Config\Config;
use Semiorbit\Data\DataSet;
use Semiorbit\Output\View;


/**
 * Restful Controller Class
 * Controller class is a super class that all controllers in the application should extend
 *
 * @author  semiorbit.com
 * @property DataSet $DataSet
 * @property Request $Request
 * @property View $View
 * @property Actions $Actions
 * @property ScaffoldRest $Scaffolding
 * @property Response $Response
 * @property array $Params
 */
abstract class RestController extends Controller
{

    public $Response;

    protected function Initialize()
    {

        $this->Actions->UseArray(Config::RestActions());

        $this->Actions->setDefaultByVerb('view', Request::VERB_GET, 1);

        $this->View->NoLayout();

        // SCAFFOLDING

        $this->Scaffolding = new ScaffoldRest($this);


    }



    public function Show()
    {

    }

    public function Store()
    {

    }

    public function Update()
    {

    }

    public function Delete()
    {

    }


}
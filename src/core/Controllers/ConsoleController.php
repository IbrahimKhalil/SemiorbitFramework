<?php

namespace Semiorbit\Controllers;



use Semiorbit\Base\Application;
use Semiorbit\Config\Config;
use Semiorbit\Console\ConsoleRegistry;
use Semiorbit\Http\Controller;


class ConsoleController extends Controller
{

    public function onStart()
    {

        // Console is allowed only in development

        if (Config::Environment() === Config::ENV_PRODUCTION)

            Application::Abort(403);


        $this->Actions->ExplicitMode()->Define(array(

            'index' => array('method' => 'Index', 'pms' => 'command')

        ));

    }

    public function Index()
    {



    }


}
<?php
/*
 *------------------------------------------------------------------------------------------------
 * SEMIORBIT                       				 					 semiorbit.com
 *------------------------------------------------------------------------------------------------
 */

namespace Semiorbit\Http;


use Semiorbit\Form\Form;

abstract class ScaffoldingProvider
{


    protected $_Controller;

    protected $_Disabled = array("Index"=>false);



    abstract public function Index();

    
    
    final public function __construct(Controller $controller)
    {
        $this->UseController($controller);
        
        $this->onStart();
    }
    
    public function onStart()
    {
        // 
    }
    
    public function UseController(Controller $controller)
    {
        $this->_Controller = $controller;
        
        return $this;
    }

    /**
     * @return BaseController
     */

    public function ActiveController()
    {
        return $this->_Controller;
    }


    public function Disable($method = null, $disable = true)
    {
        if ($method) $this->_Disabled[$method] = $disable;

        else $this->_Disabled = array("Index"=>true);

        return $this;
    }

    public function IsEnabled($method)
    {
        return ! $this->_Disabled[$method];
    }


}
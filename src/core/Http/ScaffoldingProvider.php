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

    protected $_FormOptions = array();

    protected $_Controller;

    protected $_Disabled = array("Index"=>false, "Edit"=>false, "Delete"=>False,

                                "Show"=>false, "ListView"=>false, "TableView"=>false);



    abstract public function Index();

    abstract public function Show();

    abstract public function Edit();

    abstract public function Delete();

    abstract public function ListView();

    abstract public function TableView();
    
    
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
     * @return Controller
     */

    public function ActiveController()
    {
        return $this->_Controller;
    }


    public function FormOptions()
    {

        if (!isset($this->_FormOptions[Form::VERIFY]) && isset ($this->ActiveController()->Request->Action['options']['verify'])) $this->_FormOptions[Form::VERIFY] = $this->ActiveController()->Request->Action['options']['verify'];

        if (!isset($this->_FormOptions[Form::SUBMIT_LABEL]) && isset ($this->ActiveController()->Request->Action['options']['submit_label'])) $this->_FormOptions[Form::SUBMIT_LABEL] = $this->ActiveController()->Request->Action['options']['submit_label'];

        if (!isset($this->_FormOptions[Form::ACTION]) && isset ($this->ActiveController()->Request->Action['options']['action_filename'])) $this->_FormOptions[Form::ACTION] = $this->ActiveController()->Request->Action['options']['action_filename'];

        return $this->_FormOptions;

    }

    public function setFormOptions(array $form_options)
    {
        $this->_FormOptions = $form_options;

        return $this;
    }

    public function Disable($method = null, $disable = true)
    {
        if ($method) $this->_Disabled[$method] = $disable;

        else $this->_Disabled = array("Index"=>true, "Edit"=>true, "Delete"=>true,

                                    "Show"=>true, "ListView"=>true, "TableView"=>true);

        return $this;
    }

    public function IsEnabled($method)
    {
        return ! $this->_Disabled[$method];
    }

    public function DisableEdit($disable = true) { return $this->Disable('Edit', $disable); }

    public function DisableDelete($disable = true) { return $this->Disable('Delete', $disable); }

    public function DisableShow($disable = true) { return $this->Disable('Show', $disable); }

    public function DisableIndex($disable = true) { return $this->Disable('Index', $disable); }

    public function DisableListView($disable = true) { return $this->Disable('ListView', $disable); }

    public function DisableTableView($disable = true) { return $this->Disable('TableView', $disable); }



    public function EnableEdit($enable = true) { return $this->Disable('Edit', $enable); }

    public function EnableDelete($enable = true) { return $this->Disable('Delete', $enable); }

    public function EnableShow($enable = true) { return $this->Disable('Show', $enable); }

    public function EnableIndex($enable = true) { return $this->Disable('Index', $enable); }

    public function EnableListView($enable = true) { return $this->Disable('ListView', $enable); }

    public function EnableTableView($enable = true) { return $this->Disable('TableView', $enable); }


}
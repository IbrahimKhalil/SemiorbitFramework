<?php
/*
 *------------------------------------------------------------------------------------------------
 * SEMIORBIT                       				 					 semiorbit.com
 *------------------------------------------------------------------------------------------------
 */

namespace Semiorbit\Http;


use Semiorbit\Form\Form;

abstract class ScaffoldRestProvider extends ScaffoldingProvider
{

    
    
    protected $_Disabled = array("Index"=>false, "Store"=>false, "Delete"=>False,

                                "Show"=>false, "Update"=>false);



    abstract public function Show();

    abstract public function Store();

    abstract public function Delete();

    abstract public function Update();
    




   

    public function Disable($method = null, $disable = true)
    {
        if ($method) $this->_Disabled[$method] = $disable;

        else $this->_Disabled = array("Index"=>true, "Store"=>true, "Delete"=>true,

                                    "Show"=>true, "Update"=>true);

        return $this;
    }



    public function DisableStore($disable = true) { return $this->Disable('Store', $disable); }

    public function DisableDelete($disable = true) { return $this->Disable('Delete', $disable); }

    public function DisableShow($disable = true) { return $this->Disable('Show', $disable); }

    public function DisableIndex($disable = true) { return $this->Disable('Index', $disable); }

    public function DisableUpdate($disable = true) { return $this->Disable('Update', $disable); }



    public function EnableStore($enable = true) { return $this->Disable('Store', $enable); }

    public function EnableDelete($enable = true) { return $this->Disable('Delete', $enable); }

    public function EnableShow($enable = true) { return $this->Disable('Show', $enable); }

    public function EnableIndex($enable = true) { return $this->Disable('Index', $enable); }

    public function EnableUpdate($enable = true) { return $this->Disable('Update', $enable); }


}
<?php
/*
*-----------------------------------------------------------------------------------------------
* FIELD - SEMIORBIT FIELD                    			  						    semiorbit.com
*-----------------------------------------------------------------------------------------------
* 
*/

namespace Semiorbit\Field;

use Semiorbit\Output\TableViewCol;




/**
 * Class ID
 * @package Semiorbit\Field
 *
 * @method ID  setProps($array)
 * @method ID  UseTemplate($form_template = null)
 * @method ID  UseDataSet(\Semiorbit\Data\DataSet $dataset)
 * @method ID  UseHtmlBuilder(callable $html_builder_func)
 * @method ID  ResetToDefault()
 * @method ID  setName($value)
 * @method ID  setCaption($value)
 * @method ID  setControl($value)
 * @method ID  setTag($value)
 * @method ID  setValue($value)
 * @method ID  setType($value)
 * @method ID  setRequired($value)
 * @method ID  setGroup($value)
 * @method ID  setPermission($value)
 * @method ID  setTemplate($value)
 * @method ID  setCssClass($value)
 * @method ID  setID($value)
 * @method ID  setValidate($value)
 * @method ID  setUnique($value)
 * @method ID  setDefaultValue($value)
 * @method ID  setNote($value)
 * @method ID  setIsTitle($value)
 * @method ID  setIsID($value)
 * @method ID  setReadOnly($value = true)
 * @method ID  setView($value)
 * @method ID  setErr($key, $value)
 * @method ID  NoControl()
 * @method ID  Hide()
 * @method ID  UseTableViewCol(\Semiorbit\Output\TableView $col = null)
 * @method ID  HideColumn()
 * @method ID  ShowColumn()
 * @method ID  setControlCssClass($value)
 */

class ID extends Field
{
	
	public $Control = Control::NONE;

    public $IsID = true;

    public $AutoIncrement = false;

    public $Required = true;

    public $DriverUniqueId = false;
	
	
	public function PreRender()
	{
	
		if ( is_empty($this->Control) ) $this->Control = Control::NONE;

        if ( is_empty($this->IsID) ) $this->IsID = true;
			
		if ( is_empty($this->Type) ) $this->Type = DataType::VARCHAR;
	
	}

    public function CheckRequiredValue()
    {

        if ( $this->Required && $this->AutoIncrement && empty($this->Value) )

            if ( ! $this->ActiveDataSet() || ( $this->ActiveDataSet() &&  $this->ActiveDataSet()->IsNew() ) ) return true;

        return parent::CheckRequiredValue();

    }

    public function DefaultTableViewCol()
    {
        return TableViewCol::Create($this)->Hide();
    }


    /**
     * @param $value
     * @return ID
     */

    public function setAutoIncrement($value)
    {

        $this->AutoIncrement	 =  ( $value );

        if ( $this->AutoIncrement ) $this->Type = DataType::INT;

        return $this;

    }

    /**
     * Instead of php uniqid() function, Database server unique id generator could be used. Database <b>driver UniqueID() function</b>
     * will be used to request a new unique id from server.
     *
     * @param bool|string $value TRUE, FALSE or Server unique id version or function name as string <br/>eg. "UUID_SHORT()"
     * @return ID
     */

    public function setDriverUniqueId($value = true)
    {
        $this->DriverUniqueId = $value;

        return $this;
    }

}
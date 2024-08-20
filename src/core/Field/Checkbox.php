<?php
/*
*-----------------------------------------------------------------------------------------------
* FIELD - SEMIORBIT FIELD                    			  						    semiorbit.com
*-----------------------------------------------------------------------------------------------
* 
*/

namespace Semiorbit\Field;





/**
 * Class Checkbox
 * @package Semiorbit\Field
 *
 * @method Checkbox  setProps($array)
 * @method Checkbox  UseTemplate($form_template = null)
 * @method Checkbox  UseDataSet(\Semiorbit\Data\DataSet $dataset)
 * @method Checkbox  UseHtmlBuilder(callable $html_builder_func)
 * @method Checkbox  ResetToDefault()
 * @method Checkbox  setName($value)
 * @method Checkbox  setCaption($value)
 * @method Checkbox  setControl($value)
 * @method Checkbox  setTag($value)
 * @method Checkbox  setValue($value)
 * @method Checkbox  setType($value)
 * @method Checkbox  setRequired($value = true)
 * @method Checkbox  setGroup($value)
 * @method Checkbox  setPermission($roles, $permissions = null)
 * @method Checkbox  setTemplate($value)
 * @method Checkbox  setCssClass($value)
 * @method Checkbox  setID($value)
 * @method Checkbox  setValidate($value)
 * @method Checkbox  setUnique($value)
 * @method Checkbox  setDefaultValue($value)
 * @method Checkbox  setNote($value)
 * @method Checkbox  setIsTitle($value)
 * @method Checkbox  setIsID($value)
 * @method Checkbox  setReadOnly($value = true)
 * @method Checkbox  setView($value)
 * @method Checkbox  setErr($key, $value)
 * @method Checkbox  NoControl()
 * @method Checkbox  Hide()
 * @method Checkbox  UseTableViewCol(\Semiorbit\Output\TableView $col = null)
 * @method Checkbox  HideColumn()
 * @method Checkbox  ShowColumn()
 * @method Checkbox  setControlCssClass($value)
 */

class Checkbox extends Field
{
	
	public $Control = Control::CHECKBOX;
	
	public $Type = DataType::BOOL;
	
	
	
	public function PreRender()
	{
		
		if ( is_empty($this->Control) ) $this->Control = Control::CHECKBOX;
			
		if ( is_empty($this->Type) ) $this->Type = DataType::BOOL;

		if ( empty($this->Value) && $this->Control = Control::CHECKBOX ) $this->Value = 0;
		
	}

}

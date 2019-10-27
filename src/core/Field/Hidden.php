<?php
/*
*-----------------------------------------------------------------------------------------------
* FIELD - SEMIORBIT FIELD                    			  						    semiorbit.com
*-----------------------------------------------------------------------------------------------
* 
*/

namespace Semiorbit\Field;




/**
 * Class Hidden
 * @package Semiorbit\Field
 *
 * @method Hidden  setProps($array)
 * @method Hidden  UseTemplate($form_template = null)
 * @method Hidden  UseDataSet(\Semiorbit\Data\DataSet $dataset)
 * @method Hidden  UseHtmlBuilder(callable $html_builder_func)
 * @method Hidden  ResetToDefault()
 * @method Hidden  setName($value)
 * @method Hidden  setCaption($value)
 * @method Hidden  setControl($value)
 * @method Hidden  setTag($value)
 * @method Hidden  setValue($value)
 * @method Hidden  setType($value)
 * @method Hidden  setRequired($value)
 * @method Hidden  setGroup($value)
 * @method Hidden  setPermission($value)
 * @method Hidden  setTemplate($value)
 * @method Hidden  setCssClass($value)
 * @method Hidden  setID($value)
 * @method Hidden  setValidate($value)
 * @method Hidden  setUnique($value)
 * @method Hidden  setDefaultValue($value)
 * @method Hidden  setNote($value)
 * @method Hidden  setIsTitle($value)
 * @method Hidden  setIsID($value)
 * @method Hidden  setReadOnly($value = true)
 * @method Hidden  setView($value)
 * @method Hidden  setErr($key, $value)
 * @method Hidden  NoControl()
 * @method Hidden  Hide()
 * @method Hidden  UseTableViewCol(\Semiorbit\Output\TableView $col = null)
 * @method Hidden  HideColumn()
 * @method Hidden  ShowColumn()
 * @method Hidden  setControlCssClass($value)
 * @method Hidden  setMaxLength($value)
 * @method Hidden  setUnsigned($value = true)
 */

class Hidden extends Field
{
	
	public $Control = Control::HIDDEN;
	
	
	public function PreRender()
	{
	
		if ( is_empty($this->Control) ) $this->Control = Control::HIDDEN;
			
		if ( is_empty($this->Type) ) $this->Type = DataType::VARCHAR;
	
	}
	
	
}
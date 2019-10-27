<?php
/*
*-----------------------------------------------------------------------------------------------
* FIELD - SEMIORBIT FIELD                    			  						    semiorbit.com
*-----------------------------------------------------------------------------------------------
* 
*/

namespace Semiorbit\Field;




/**
 * Class Custom
 * @package Semiorbit\Field
 *
 * @method Custom  setProps($array)
 * @method Custom  UseTemplate($form_template = null)
 * @method Custom  UseDataSet(\Semiorbit\Data\DataSet $dataset)
 * @method Custom  UseHtmlBuilder(callable $html_builder_func)
 * @method Custom  ResetToDefault()
 * @method Custom  setName($value)
 * @method Custom  setCaption($value)
 * @method Custom  setControl($value)
 * @method Custom  setTag($value)
 * @method Custom  setValue($value)
 * @method Custom  setType($value)
 * @method Custom  setRequired($value)
 * @method Custom  setGroup($value)
 * @method Custom  setPermission($value)
 * @method Custom  setTemplate($value)
 * @method Custom  setCssClass($value)
 * @method Custom  setID($value)
 * @method Custom  setValidate($value)
 * @method Custom  setUnique($value)
 * @method Custom  setDefaultValue($value)
 * @method Custom  setNote($value)
 * @method Custom  setIsTitle($value)
 * @method Custom  setIsID($value)
 * @method Custom  setReadOnly($value = true)
 * @method Custom  setView($value)
 * @method Custom  setErr($key, $value)
 * @method Custom  NoControl()
 * @method Custom  Hide()
 * @method Custom  UseTableViewCol(\Semiorbit\Output\TableView $col = null)
 * @method Custom  HideColumn()
 * @method Custom  ShowColumn()
 * @method Custom  setControlCssClass($value)
 * @method Custom  setMaxLength($value)
 * @method Custom  setUnsigned($value = true)
 */

class Custom extends Field
{
	
	public $Control = Control::CUSTOM;
	
	public $HtmlCtrl;

	
	
	public function PreRender()
	{
	
		if ( is_empty($this->Control) ) $this->Control = Control::CUSTOM;
			
		if ( is_empty($this->Type) ) $this->Type = DataType::VARCHAR;
	
	}


    /**
     * @param $value
     * @return Custom
     */

	public function setHtmlCtrl($value)		 { $this->HtmlCtrl	 = strval( $value ); return $this; }
	
}

<?php
/*
*-----------------------------------------------------------------------------------------------
* FIELD - SEMIORBIT FIELD                    			  						    semiorbit.com
*-----------------------------------------------------------------------------------------------
* 
*/

namespace Semiorbit\Field;





/**
 * Class Editor
 * @package Semiorbit\Field
 *
 * @method Editor  setProps($array)
 * @method Editor  UseTemplate($form_template = null)
 * @method Editor  UseDataSet(\Semiorbit\Data\DataSet $dataset)
 * @method Editor  ResetToDefault()
 * @method Editor  setName($value)
 * @method Editor  setCaption($value)
 * @method Editor  setControl($value)
 * @method Editor  setTag($value)
 * @method Editor  setValue($value)
 * @method Editor  setType($value)
 * @method Editor  setRequired($value)
 * @method Editor  setGroup($value)
 * @method Editor  setPermission($value)
 * @method Editor  setTemplate($value)
 * @method Editor  setCssClass($value)
 * @method Editor  setID($value)
 * @method Editor  setValidate($value)
 * @method Editor  setUnique($value)
 * @method Editor  setDefaultValue($value)
 * @method Editor  setNote($value)
 * @method Editor  setIsTitle($value)
 * @method Editor  setIsID($value)
 * @method Editor  setReadOnly($value = true)
 * @method Editor  setView($value)
 * @method Editor  setErr($key, $value)
 * @method Editor  NoControl()
 * @method Editor  Hide()
 * @method Editor  UseTableViewCol(\Semiorbit\Output\TableView $col = null)
 * @method Editor  HideColumn()
 * @method Editor  ShowColumn()
 * @method Editor  setControlCssClass($value)
 */

class Editor extends Field
{

    public $Control = Control::EDITOR;

    public $Type = DataType::TEXT;

    public $MaxLength;

    public $AllowHtml = true;


    public function PreRender()
    {

        if (is_empty($this->Control)) $this->Control = Control::EDITOR;

        if (is_empty($this->Type)) $this->Type = DataType::TEXT;

        if (is_empty($this->MaxLength)) $this->MaxLength = ($this->Type == DataType::VARCHAR) ? 500 : 65535;

    }


    public function InputAttrs(array $include_attrs = [], array $exclude_attrs = [])
    {

        $attrs = array(Field::MAX_LENGTH);

        $include_attrs = array_merge($attrs, $include_attrs);

        return $this->BaseAttrs($include_attrs, $exclude_attrs);
    }

    public function DefaultHtmlBuilder()
    {
        return $this->AllowHtml ?  ( $this->Value ?: '' ) : nl2br( htmlentities( $this->Value ) );
    }

    /**
     * @param $value
     * @return Editor
     */

    public function setMaxLength($value)
    {
        $this->MaxLength = strval($value);

        return $this;
    }


}

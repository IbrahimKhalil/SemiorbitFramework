<?php
/*
*-----------------------------------------------------------------------------------------------
* FIELD - SEMIORBIT FIELD                    			  						    semiorbit.com
*-----------------------------------------------------------------------------------------------
* 
*/

namespace Semiorbit\Field;





/**
 * Class TextArea
 * @package Semiorbit\Field
 *
 * @method TextArea  setProps($array)
 * @method TextArea  UseTemplate($form_template = null)
 * @method TextArea  UseDataSet(\Semiorbit\Data\DataSet $dataset)
 * @method TextArea  UseHtmlBuilder(callable $html_builder_func)
 * @method TextArea  ResetToDefault()
 * @method TextArea  setName($value)
 * @method TextArea  setCaption($value)
 * @method TextArea  setControl($value)
 * @method TextArea  setTag($value)
 * @method TextArea  setValue($value)
 * @method TextArea  setType($value)
 * @method TextArea  setRequired($value = true)
 * @method TextArea  setGroup($value)
 * @method TextArea  setPermission($roles, $permissions = null)
 * @method TextArea  setTemplate($value)
 * @method TextArea  setCssClass($value)
 * @method TextArea  setID($value)
 * @method TextArea  setValidate($value)
 * @method TextArea  setUnique($value)
 * @method TextArea  setDefaultValue($value)
 * @method TextArea  setNote($value)
 * @method TextArea  setIsTitle($value = true)
 * @method TextArea  setIsID($value)
 * @method TextArea  setReadOnly($value = true)
 * @method TextArea  setView($value)
 * @method TextArea  setErr($key, $value)
 * @method TextArea  NoControl()
 * @method TextArea  Hide()
 * @method TextArea  UseTableViewCol(\Semiorbit\Output\TableView $col = null)
 * @method TextArea  HideColumn()
 * @method TextArea  ShowColumn()
 * @method TextArea  setControlCssClass($value)
 * @method TextArea  setInputCssClass($value)
 */

class TextArea extends Field
{

    public $Control = Control::TEXTAREA;

    public $Type = DataType::TEXT;

    public $MaxLength;

    public $PlaceHolder;

    public $AllowHtml;


    public function PreRender()
    {

        if (is_empty($this->Control)) $this->Control = Control::TEXTAREA;

        if (is_empty($this->Type)) $this->Type = DataType::TEXT;

        if (is_empty($this->MaxLength)) $this->MaxLength = ($this->Type == DataType::VARCHAR) ? 255 : 0;

    }


    public function InputAttrs(array $include_attrs = [], array $exclude_attrs = [])
    {

        $attrs = array(Field::PLACE_HOLDER, Field::MAX_LENGTH);

        $include_attrs = array_merge($attrs, $include_attrs);

        return $this->BaseAttrs($include_attrs, $exclude_attrs);
    }

    public function DefaultHtmlBuilder()
    {
        return $this->AllowHtml ?  $this->Value : ($this->Value ? nl2br( htmlentities( $this->Value ) ) : $this->Value);
    }

    /**
     * @param $value
     * @return TextArea
     */

    public function setPlaceHolder($value)
    {
        $this->PlaceHolder = strval($value);

        return $this;
    }

    /**
     * @param $value
     * @return TextArea
     */

    public function setMaxLength($value)
    {
        $this->MaxLength = strval($value);

        return $this;
    }


    /**
     * @param $value
     * @return TextArea
     */

    public function setAllowHtml($value = true)
    {
        $this->AllowHtml = ($value);

        return $this;
    }


}

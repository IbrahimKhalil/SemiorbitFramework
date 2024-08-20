<?php
/*
*-----------------------------------------------------------------------------------------------
* FIELD - SEMIORBIT FIELD                    			  						    semiorbit.com
*-----------------------------------------------------------------------------------------------
* 
*/

namespace Semiorbit\Field;

use Semiorbit\Form\Form;
use Semiorbit\Translation\Lang;


/**
 * Class Password
 * @package Semiorbit\Field
 *
 * @method Password  setProps($array)
 * @method Password  UseTemplate($form_template = null)
 * @method Password  UseDataSet(\Semiorbit\Data\DataSet $dataset)
 * @method Password  UseHtmlBuilder(callable $html_builder_func)
 * @method Password  ResetToDefault()
 * @method Password  setName($value)
 * @method Password  setCaption($value)
 * @method Password  setControl($value)
 * @method Password  setTag($value)
 * @method Password  setValue($value)
 * @method Password  setType($value)
 * @method Password  setRequired($value = true)
 * @method Password  setGroup($value)
 * @method Password  setPermission($roles, $permissions = null)
 * @method Password  setTemplate($value)
 * @method Password  setCssClass($value)
 * @method Password  setID($value)
 * @method Password  setValidate($value)
 * @method Password  setUnique($value = true)
 * @method Password  setDefaultValue($value)
 * @method Password  setNote($value)
 * @method Password  setIsTitle($value = true)
 * @method Password  setIsID($value = true)
 * @method Password  setReadOnly($value = true)
 * @method Password  setView($value)
 * @method Password  setErr($key, $value)
 * @method Password  NoControl()
 * @method Password  Hide()
 * @method Password  UseTableViewCol(\Semiorbit\Output\TableView $col = null)
 * @method Password  HideColumn()
 * @method Password  ShowColumn()
 * @method Password  setControlCssClass($value)
 */

class Password extends Field
{

    public $Control = Control::PASSWORD;

    public $Type = DataType::VARCHAR;

    public $MaxLength;

    public $PlaceHolder;

    public $Retype = true;

    public $FillRetype = SUPER_ADMIN;

    public $RetypeSuffix = '_r';

    public $RetypeCaption;

    public $View = FieldView::HIDDEN;


    public function PreRender()
    {

        //Reset Defaults

        if (is_empty($this->Control)) $this->Control = Control::TEXT;

        if (is_empty($this->Type)) $this->Type = DataType::VARCHAR;

        if (is_empty($this->FillRetype)) $this->FillRetype = SUPER_ADMIN;

        if (is_empty($this->RetypeSuffix)) $this->RetypeSuffix = '_r';

        $retype_prefix = Lang::Trans("semiorbit::form.retype");

        if (is_empty($this->RetypeCaption)) $this->RetypeCaption = $retype_prefix . $this->LabelText();

        if ($this->Retype !== false) $this->Retype = true;

    }


    public function InputAttrs(array $include_attrs = [], array $exclude_attrs = [])
    {

        $attrs = array(Field::PLACE_HOLDER, Field::MAX_LENGTH);

        $include_attrs = array_merge($attrs, $include_attrs);

        return $this->BaseAttrs($include_attrs, $exclude_attrs);

    }

    public function RetypeInputName($hash = true, $form = null)
    {

        $retype_input_name = $this->InputName($hash, $form, $this->RetypeSuffix);

        return $retype_input_name;

    }

    public function RetypeInputValue($hash = true, $form = null)
    {

        $retype_input_value = Form::InputByName( $this->RetypeInputName($hash, $form), true, $form);

        return $retype_input_value;

    }

    public function ValidatePassword()
    {
        if ( $this->Required && is_empty($this->Value) ) return false;

        if ( $this->Retype === false ) return true;

        return $this->Value == $this->RetypeInputValue();
    }

    /**
     * @param $value
     * @return Password
     */

    public function setPlaceHolder($value)
    {
        $this->PlaceHolder = strval($value);

        return $this;
    }

    /**
     * @param $value
     * @return Password
     */

    public function setMaxLength($value)
    {
        $this->MaxLength = strval($value);

        return $this;
    }

    /**
     * @param bool $value
     * @return Password
     */

    public function setRetype($value = true)
    {
        $this->Retype = (bool) $value;

        return $this;
    }

    /**
     * @param $value
     * @return Password
     */

    public function setRetypeCaption($value)
    {
        $this->RetypeCaption = strval($value);

        return $this;
    }

    /**
     * @param $value
     * @return Password
     */

    public function setRetypeSuffix($value)
    {
        $this->RetypeSuffix = strval($value);

        return $this;
    }

    /**
     * @param $value
     * @return Password
     */

    public function setFillRetype($value)
    {
        $this->FillRetype = strval($value);

        return $this;
    }


}

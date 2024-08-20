<?php
/*
*-----------------------------------------------------------------------------------------------
* FIELD - SEMIORBIT FIELD                    			  						    semiorbit.com
*-----------------------------------------------------------------------------------------------
* 
*/

namespace Semiorbit\Field;





use Semiorbit\Data\DataSet;
use Semiorbit\Db\DB;

/**
 * Class Text
 * @package Semiorbit\Field
 *
 * @method Text  setProps($array)
 * @method Text  UseTemplate($form_template = null)
 * @method Text  UseDataSet(\Semiorbit\Data\DataSet $dataset)
 * @method Text  UseHtmlBuilder(callable $html_builder_func)
 * @method Text  ResetToDefault()
 * @method Text  setName($value)
 * @method Text  setCaption($value)
 * @method Text  setControl($value)
 * @method Text  setTag($value)
 * @method Text  setValue($value)
 * @method Text  setType($value)
 * @method Text  setRequired($value = true)
 * @method Text  setGroup($value)
 * @method Text  setPermission($roles, $permissions = null)
 * @method Text  setTemplate($value)
 * @method Text  setCssClass($value)
 * @method Text  setID($value)
 * @method Text  setValidate($value)
 * @method Text  setUnique($value = true)
 * @method Text  setDefaultValue($value)
 * @method Text  setNote($value)
 * @method Text  setIsTitle($value = true)
 * @method Text  setIsID($value = true)
 * @method Text  setReadOnly($value = true)
 * @method Text  setView($value)
 * @method Text  setErr($key, $value)
 * @method Text  NoControl()
 * @method Text  Hide()
 * @method Text  UseTableViewCol(\Semiorbit\Output\TableView $col = null)
 * @method Text  HideColumn()
 * @method Text  ShowColumn()
 * @method Text  setControlCssClass($value)
 * @method Text  setInputCssClass($value)
 * @method Text  setUnsigned($value = true)
 */

class Text extends Field
{

    public $Control = Control::TEXT;

    public $Type = DataType::VARCHAR;

    public $MaxLength = 255;

    public $PlaceHolder;

    public $AllowHtml;

    public $InputMode;

    public $Pattern;

    use Datalist;


    public function PreRender()
    {

        if (is_empty($this->Control)) $this->Control = Control::TEXT;

        if (is_empty($this->Type)) $this->Type = DataType::VARCHAR;

        if (is_empty($this->MaxLength)) $this->MaxLength = ($this->Type == DataType::VARCHAR) ? 255 : 0;

        if ($this->DatalistSource) $this->FillDatalist();

    }


    public function InputAttrs(array $include_attrs = [], array $exclude_attrs = [])
    {

        $attrs = array(Field::PLACE_HOLDER, Field::MAX_LENGTH, Field::INPUT_MODE, Field::PATTERN);

        $this->AddDatalistAttrs($attrs);

        $include_attrs = array_merge($attrs, $include_attrs);

        return $this->BaseAttrs($include_attrs, $exclude_attrs);

    }

    public function DefaultHtmlBuilder()
    {
        return $this->AllowHtml ? strval( $this->Value ) : htmlentities( strval( $this->Value ) );
    }


    /**
     * @param $value
     * @return Text
     */

    public function setPlaceHolder($value)
    {
        $this->PlaceHolder = strval($value);

        return $this;
    }


    /**
     * @param $value
     * @return Text
     */

    public function setInputMode($value)
    {
        $this->InputMode = strval($value);

        return $this;
    }

    /**
     * @param $value
     * @return Text
     */

    public function setPattern($value)
    {
        $this->Pattern = strval($value);

        return $this;
    }


    /**
     * @param $value
     * @return Text
     */

    public function setMaxLength($value)
    {
        $this->MaxLength = strval($value);

        return $this;
    }

    /**
     * @param $value
     * @return static
     */

    public function setAllowHtml($value)
    {
        $this->AllowHtml = ($value);

        return $this;
    }


}

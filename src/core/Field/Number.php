<?php
/*
*-----------------------------------------------------------------------------------------------
* FIELD - SEMIORBIT FIELD                    			  						    semiorbit.com
*-----------------------------------------------------------------------------------------------
* 
*/

namespace Semiorbit\Field;





/**
 * Class Number
 * @package Semiorbit\Field
 *
 * @method \Semiorbit\Field\Number  setProps($array)
 * @method \Semiorbit\Field\Number  UseTemplate($form_template = null)
 * @method \Semiorbit\Field\Number  UseDataSet(\Semiorbit\Data\DataSet $dataset)
 * @method \Semiorbit\Field\Number  UseHtmlBuilder(callable $html_builder_func)
 * @method \Semiorbit\Field\Number  ResetToDefault()
 * @method \Semiorbit\Field\Number  setName($value)
 * @method \Semiorbit\Field\Number  setCaption($value)
 * @method \Semiorbit\Field\Number  setControl($value)
 * @method \Semiorbit\Field\Number  setTag($value)
 * @method \Semiorbit\Field\Number  setValue($value)
 * @method \Semiorbit\Field\Number  setType($value)
 * @method \Semiorbit\Field\Number  setRequired($value)
 * @method \Semiorbit\Field\Number  setGroup($value)
 * @method \Semiorbit\Field\Number  setPermission($value)
 * @method \Semiorbit\Field\Number  setTemplate($value)
 * @method \Semiorbit\Field\Number  setCssClass($value)
 * @method \Semiorbit\Field\Number  setID($value)
 * @method \Semiorbit\Field\Number  setValidate($value)
 * @method \Semiorbit\Field\Number  setUnique($value)
 * @method \Semiorbit\Field\Number  setDefaultValue($value)
 * @method \Semiorbit\Field\Number  setNote($value)
 * @method \Semiorbit\Field\Number  setIsTitle($value)
 * @method \Semiorbit\Field\Number  setIsID($value)
 * @method \Semiorbit\Field\Number  setReadOnly($value = true)
 * @method \Semiorbit\Field\Number  setView($value)
 * @method \Semiorbit\Field\Number  setErr($key, $value)
 * @method \Semiorbit\Field\Number  NoControl()
 * @method \Semiorbit\Field\Number  Hide()
 * @method \Semiorbit\Field\Number  UseTableViewCol(\Semiorbit\Output\TableView $col = null)
 * @method \Semiorbit\Field\Number  HideColumn()
 * @method \Semiorbit\Field\Number  ShowColumn()
 * @method \Semiorbit\Field\Number  setControlCssClass($value)
 */

class Number extends Field
{

    public $Control = Control::NUMBER;

    public $Type = DataType::INT;

    public $Min;

    public $Max;

    public $Step;

    protected $_Decimals = 0;

    protected $_DecPoint = '.';

    protected $_ThousandsSep = ',';

    protected $_EnableNumberFormat = false;


    public function PreRender()
    {

        if (empty($this->Step))

            $this->Step = (in_array($this->Type, array(DataType::DECIMAL, DataType::FLOAT, DataType::DOUBLE))) ? 'any' : '1';


        if (is_empty($this->Control)) $this->Control = Control::NUMBER;

        if (is_empty($this->Type)) $this->Type = DataType::INT;

    }


    public function InputAttrs(array $include_attrs = [], array $exclude_attrs = [])
    {

        $attrs = array(Field::MAX => strval($this->Max), Field::MIN => strval($this->Min), Field::STEP => $this->Step);

        $include_attrs = array_merge($attrs, $include_attrs);

        return $this->BaseAttrs($include_attrs, $exclude_attrs);

    }

    public function DefaultHtmlBuilder()
    {
        return $this->_EnableNumberFormat ?

            number_format($this->Value, $this->_Decimals, $this->_DecPoint, $this->_ThousandsSep) : strval($this->Value);
    }

    public function NumberFormat($decimals = 0 , $dec_point = '.' , $thousands_sep = ',')
    {

        $this->_EnableNumberFormat = true;

        $this->_Decimals = $decimals;

        $this->_DecPoint = $dec_point;

        $this->_ThousandsSep = $thousands_sep;

        return $this;

    }

    public function DisableNumberFormat()
    {
        $this->_EnableNumberFormat = false;

        return $this;
    }


    /**
     * @param $value
     * @return $this
     */

    public function setMin($value)
    {
        $this->Min = $value;

        return $this;
    }

    /**
     * @param $value
     * @return $this
     */

    public function setMax($value)
    {
        $this->Max = $value;

        return $this;
    }

    /**
     * @param $value
     * @return $this
     */

    public function setStep($value)
    {
        $this->Step = $value;

        return $this;
    }




}

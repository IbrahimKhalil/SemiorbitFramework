<?php
/*
*-----------------------------------------------------------------------------------------------
* FIELD - SEMIORBIT FIELD                    			  						    semiorbit.com
*-----------------------------------------------------------------------------------------------
* 
*/

namespace Semiorbit\Field;

use Semiorbit\Form\Form;
use Semiorbit\Support\Str;


/**
 * Class Select
 * @package Semiorbit\Field
 *
 * @method Select  setProps($array)
 * @method Select  UseTemplate($form_template = null)
 * @method Select  UseDataSet(\Semiorbit\Data\DataSet $dataset)
 * @method Select  UseHtmlBuilder(callable $html_builder_func)
 * @method Select  ResetToDefault()
 * @method Select  setName($value)
 * @method Select  setCaption($value)
 * @method Select  setControl($value)
 * @method Select  setTag($value)
 * @method Select  setValue($value)
 * @method Select  setRequired($value = true)
 * @method Select  setGroup($value)
 * @method Select  setPermission($roles, $permissions = null)
 * @method Select  setTemplate($value)
 * @method Select  setCssClass($value)
 * @method Select  setID($value)
 * @method Select  setValidate($value)
 * @method Select  setUnique($value)
 * @method Select  setDefaultValue($value)
 * @method Select  setNote($value)
 * @method Select  setIsTitle($value = true)
 * @method Select  setIsID($value = true)
 * @method Select  setReadOnly($value = true)
 * @method Select  setView($value)
 * @method Select  setErr($key, $value)
 * @method Select  setForeignKey($table, $value_field, $text_field)
 * @method Select  setFKeyFilter($value)
 * @method Select  NoControl()
 * @method Select  Hide()
 * @method Select  UseTableViewCol(\Semiorbit\Output\TableView $col = null)
 * @method Select  HideColumn()
 * @method Select  ShowColumn()
 * @method Select  setControlCssClass($value)
 * @method Select  setMaxLength($value)
 * @method Select  setUnsigned($value = true)
 */
class Select extends Field
{

    public $Control = Control::SELECT;

    public $Type = DataType::VARCHAR;

    public $MaxLength = 255;

    protected $_Options = array();

    public $Format;

    public $SelectedFormat;

    public $Multiple = false;

    public $UndefinedValues = false;

    public $Separator = ',';

    public $SelectedFirst = false;

    protected $_SelectedValues = array();

    protected $_OptionTextBuilder;

    protected $_TypeDefined = false;

    use ForeignKey;


    const FKEY_PRELOAD_ALL = 0;

    const FKEY_LAZY_LOADING = 1;

    const FKEY_LOAD_MISSING = 2;

    const FKEY_EAGER_LOADING = 3;

    const FKEY_FROM_RESULT = 4;

    const FKEY_FROM_ARRAY = 5;


    public function PreRender()
    {

        if (is_empty($this->Control)) $this->Control = Control::SELECT;

        if (is_empty($this->Type)) $this->Type = DataType::VARCHAR;

        if (is_empty($this->_Options)) $this->_Options = array();

        if ($this->UndefinedValues) {

            $undefined_options = array_udiff($this->SelectedValues(), $this->_Options, 'strcasecmp');

            foreach ($undefined_options as $undefined_option) {

                if (trim($undefined_option) != '') $this->setOption(Option::set($undefined_option, $undefined_option)->setAtBottom());

            }

        }


    }

    public function InputAttrs(array $include_attrs = [], array $exclude_attrs = [])
    {

        $attrs = array(Field::MULTIPLE => $this->Multiple ? Field::MULTIPLE : '');

        if ($this->Multiple) {

            $attrs['name'] = $this->InputName() . '[]';

        }

        $include_attrs = array_merge($attrs, $include_attrs);

        return $this->BaseAttrs($include_attrs, $exclude_attrs);

    }

    public function InputValue($hash = true, $form = null, $method = null)
    {

        if ($this->Multiple) {

            $data = Form::InputByName($this->InputName($hash, $form), true, $form, $method);

            return implode($this->Separator, (array) $data);

        }

        return Form::InputByName($this->InputName($hash, $form), true, $form, $method);

    }

    /**
     * @param bool $multiple
     * @return $this
     */

    public function setMultiple($multiple = true)
    {

        $this->Type = DataType::TEXT;

        $this->MaxLength = 65535;

        $this->Multiple = $multiple;

        return $this;
    }

    /**
     * @param bool $add
     * @return $this
     */

    public function setUndefinedValues($add = true)
    {
        $this->UndefinedValues = $add;

        return $this;
    }

    /**
     * @param bool $order_selected_first
     * @return $this
     */

    public function setSelectedFirst($order_selected_first = true)
    {
        $this->SelectedFirst = $order_selected_first;

        return $this;
    }


    public function Options(array $selector = null)
    {

        if ($selector == null) return $this->_Options;

        $selected_options = array();

        foreach ($this->_Options as $opt_key => $opt) {

            /** @var Option $opt */

            foreach ($selector as $prop => $val) {

                if ($opt->{$prop} == $val) $selected_options[$opt_key] = $opt;

            }

        }

        return $selected_options;


    }

    /**
     * @param $param1
     * @param null $param2
     * @param bool $disabled
     * @return Select
     */

    public function setOption($param1, $param2 = null, $disabled = false)
    {

        if ($param1 instanceof Option) $opt = $param1;

        elseif ($param2 instanceof Option) $opt = $param2;

        else $opt = Option::set($param1, $param2, $disabled);


        if ($opt->Key === null) {

            $this->_Options[] = $opt;

            end($this->_Options);

            $opt->setKey(key($this->_Options));

            reset($this->_Options);

        } else {

            $this->_Options[$opt->Key] = $opt;

        }

        return $this;

    }

    public function SelectedOption($number = 0, $format_value = true)
    {

        $options = array_values($this->SelectedOptions($format_value, $number));

        return $options[$number];

    }

    public function SelectedOptions($format_value = true, $selected_option_number = null)
    {

        $format = (!empty($this->SelectedFormat) && $format_value) ? $this->SelectedFormat : false;

        $opt_list = array();

        $selected_values = $this->SelectedValues();

        if ($selected_option_number !== null) $selected_values = array($selected_values[$selected_option_number]);

        foreach ($selected_values as $value) {

            if ( isset($this->_Options[$value]) ) {

                $opt_text =  $this->_Options[$value];

                $opt_list[$value] = !empty($format) ? sprintf($format, ($this->Trans($opt_text)))

                    : ($this->Trans($opt_text));

            } else {

                $opt_list[$value] = $this->FKeyText($value);

            }

        }

        return $opt_list;

    }


    public function SelectedValues()
    {

        if ($this->Multiple) {


            $cache_key = $this->Separator . $this->Value;

            if (isset($this->_SelectedValues[$cache_key])) {

                return $this->_SelectedValues[$cache_key];

            } else {

                $this->_SelectedValues = array();

                return $this->_SelectedValues[$cache_key] = explode($this->Separator, $this->Value);

            }

        } else {

            return (array) $this->Value;

        }

    }

    public function Option($key)
    {
        return $this->_Options[$key] ?? null;
    }

    public function OptionText($key)
    {
        return $this->Trans($this->Option($key));
    }


    public function OptionByValue($val)
    {

    }

    public function hasOption($key)
    {
        return isset($this->_Options[$key]);
    }


    /**
     * @param array $options_array
     * @return \Semiorbit\Field\Select
     */

    public function setOptions(array $options_array)
    {
        foreach ($options_array as $opt_k => $opt_v) $this->setOption($opt_k, $opt_v);

        return $this;
    }

    public function ClearOptions()
    {
        $this->_Options = [];

        return $this;
    }

    /**
     * This method uses none type strict comparision, so zero could be equal '0', null and empty string
     *
     * @param $opt_key
     * @param $opt_value
     * @param bool $enable_text_formatting
     * @param bool $disabled
     * @return string
     */
    public function OptionHtml($opt_key, $opt_value, $enable_text_formatting = true, $disabled = false)
    {

        if ($this->Multiple) {

            $selected_html = (in_array($opt_key, $this->SelectedValues())) ? "selected" : "";

        } else {

            $selected_html = ($opt_key == $this->Value) ? "selected" : "";

        }

        $format = (!$enable_text_formatting) ? '' :

            ((!empty($this->SelectedFormat) && $selected_html == "selected") ? $this->SelectedFormat : $this->Format);

        $opt_text = !empty($format) && !isset($this->_Options[$opt_key]) ? sprintf($format, htmlspecialchars($opt_value ?? ''))

            : htmlspecialchars($opt_value ?? '');

        $disabled_html = $disabled ? ' disabled ' : '';

        if ($this->_OptionTextBuilder) $opt_text = call_user_func_array($this->ActiveOptionTextBuilder(), array($opt_key, $opt_text));

        $option_html = '<option value="' . $opt_key . '" ' . $selected_html . $disabled_html . '>' . $opt_text . '</option>';

        return $option_html;

    }


    public function ActiveOptionTextBuilder()
    {
        return $this->_OptionTextBuilder;
    }

    /**
     * @param  $html_builder_func
     * @return $this
     */

    public function UseOptionTextBuilder(callable $html_builder_func)
    {
        if ($html_builder_func) $this->_OptionTextBuilder = $html_builder_func;

        return $this;
    }


    public function DefaultHtmlBuilder()
    {
        $options = $this->SelectedOptions();

        return  count($options) == 1 ? array_shift($options) : (count($options) > 1 ? Str::ArrayToList($options) : '') ;
    }

    /**
     * @param $value
     * @return Select
     */

    public function setFormat($value)
    {
        $this->Format = strval($value);

        return $this;
    }


    /**
     * @param $value
     * @return Select
     */

    public function setSelectedFormat($value)
    {
        $this->SelectedFormat = strval($value);

        return $this;
    }

    /**
     * @param $value
     * @return Select
     */

    public function setType($value)
    {
        $this->_TypeDefined = true;

        parent::setType($value);

        return $this;
    }

}

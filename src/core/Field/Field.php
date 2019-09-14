<?php
/*
*-----------------------------------------------------------------------------------------------
* FIELD - SEMIORBIT FIELD                    			  						    semiorbit.com
*-----------------------------------------------------------------------------------------------
* 
*/

namespace Semiorbit\Field;




use Semiorbit\Output\TableViewCol;
use Semiorbit\Support\AltaArray;
use Semiorbit\Data\DataSet;
use Semiorbit\Config\CFG;
use Semiorbit\Form\Form;
use Semiorbit\Form\FormTemplate;
use Semiorbit\Support\Uploader;
use Semiorbit\Translation\Lang;
use Semiorbit\Support\Str;


class Field extends AltaArray implements FieldProps
{

    public $Name;

    public $Caption;

    public $Tag;

    public $Value;

    public $Type = DataType::VARCHAR;

    public $Control = Control::TEXT;

    public $Required = false;

    public $Group;

    public $Permission;

    public $Template;

    public $CssClass;

    public $ControlCssClass;

    public $InputCssClass;

    public $ID;

    public $Validate;

    public $Unique;

    public $DefaultValue;

    public $Note;

    public $IsTitle = false;

    public $View;

    public $IsID = false;

    public $ReadOnly = false;

    public $InputIsReadOnly = false;

    public $ExcludeFromSelect = false;

    public $Err = array();

    public $InputID;


    protected $_FormTemplate;

    protected $_DataSet;

    protected $_InputNameFormat;

    protected $_InputValue;

    protected $_DataValue;

    protected $_HashedInputName;

    protected $_SrcHashedInputName;

    protected $_HtmlText;

    protected $_HtmlBuilder;

    protected $_FilterValue;

    protected $_FilteredValue;

    protected $_TableViewCol;

    protected $_DisableTaleViewCol = false;

    protected $_SelectExpr;

    protected $_WhereClauseHelperFunc;

    use FieldBuilder;


    public function __construct($field)
    {

        if (is_string($field)) {

            $this->Name = $field;

            parent::__construct(array());

        } else {

            parent::__construct($field);

            if ($field instanceof Field) {

                if ($field->ActiveDataSet()) $this->UseDataSet($field->ActiveDataSet());

                $this->UseTemplate($field->ActiveTemplate());

            }

        }

        $this->InitializeProps();

    }

    public function InitializeProps()
    {

        if ($this->ID == null) $this->ID = Str::PascalCaseKeepLang($this->Name);

        if ($this->Caption == null) $this->Caption = $this->Name;


    }


    public function setProps($array)
    {

        $this->Merge($array);

        return $this;

    }

    public function UseTemplate($form_template = null)
    {

        if (is_empty($form_template)) $form_template = $this->DefaultTemplate();

        if ($form_template instanceof FormTemplate) $this->_FormTemplate = $form_template;

        else $this->_FormTemplate = new FormTemplate($form_template);

        return $this;

    }

    /**
     * @return FormTemplate
     */

    public function ActiveTemplate()
    {

        if (is_empty($this->_FormTemplate)) $this->UseTemplate();

        return $this->_FormTemplate;

    }

    protected function DefaultTemplate()
    {

        if (!is_empty($this->Template)) {

            $def_temp = $this->Template;

        } else {

            $def_temp = Form::ActiveTemplate() ?: CFG::FormTemplate();

        }

        return $def_temp;

    }


    public function UseDataSet(DataSet $dataset)
    {

        $this->_DataSet = $dataset;

        return $this;

    }

    public function ActiveDataSet()
    {

        if ($this->_DataSet instanceof DataSet) return $this->_DataSet;

        return false;

    }


    public function RenderControl($flush_output = true, $pms = array())
    {


        if ($this->ActiveDataSet()) $this->ActiveDataSet()->onRenderControlStart($this);

        $this->SubmitInput();

        $this->PreRender();


        $html_output = $this->ActiveTemplate()->RenderControl($this, $flush_output, $pms);

        if ($this->ActiveDataSet()) $this->ActiveDataSet()->onRenderControlComplete($this);

        return $html_output;

    }

    public function PreRender()
    {

    }

    public function Attr($attr, $value)
    {

        if (is_empty($value)) $value = isset($this->{$attr}) ? $this->{$attr} : '';

        $html_output = !is_empty($value) ? $attr . ' = "' . htmlspecialchars($value) . '" ' : "";

        return $html_output;

    }

    public function Attrs(array $attrs = [])
    {

        $html_output = '';

        foreach ($attrs as $k => $v) {
            $html_output .= is_int($k) ? $this->Attr($v, $this->{$v}) : $this->Attr($k, $v);
        }

        return $html_output;

    }

    public function BaseAttrs(array $include_attrs = [], array $exclude_attrs = [])
    {

        $attrs['name'] = $this->InputName();

        $attrs['id'] = $this->InputID ?: $attrs['name'];

        $attrs['data-default'] = $this->DefaultValue;

        if ($this->InputIsReadOnly === true) $attrs['readonly'] = 'readonly';

        if ($this->Required === true) $attrs['required'] = 'required';

        $attrs = array_merge($attrs, $include_attrs);

        $attrs = array_diff($attrs, $exclude_attrs);

        $html_output = $this->Attrs($attrs);

        return $html_output;

    }

    public function InputAttrs(array $include_attrs = [], array $exclude_attrs = [])
    {
        return $this->BaseAttrs($include_attrs, $exclude_attrs);
    }

    public function ControlDefaultCssClass()
    {

        $css_class = array();

        if (!empty($this->CssClass)) $css_class[] = trim($this->CssClass);

        if (!empty($this->ControlCssClass)) $css_class[] = trim($this->ControlCssClass);

        if ($this->Required) $css_class[] = "semiorbit-field-required";

        if ($this->Validate) $css_class[] = "semiorbit-field-validate-" . $this->Validate;

        if ($this->Unique) $css_class[] = "semiorbit-field-unique";

        if ($this->Type == DataType::DOUBLE || $this->Type == DataType::FLOAT) $css_class[] = "semiorbit-field-double";

        if ($this->Type == DataType::INT) $css_class[] = "semiorbit-field-int";

        if ($this->Control == Control::EDITOR) $css_class[] = "semiorbit-field-editor";

        if (!empty($this->Err)) $css_class[] = "has-error";

        $css_class = implode(" ", $css_class);

        return $css_class;

    }

    public function InputDefaultCssClass()
    {

        $css_class = array();

        if (!empty($this->ActiveTemplate()->Settings("input_css_class"))) $css_class[] = trim($this->ActiveTemplate()->Settings("input_css_class"));

        if (!empty($this->InputCssClass)) $css_class[] = trim($this->InputCssClass);

        if ($this->Required) $css_class[] = "semiorbit-input-required";

        if ($this->Validate) $css_class[] = "semiorbit-input-validate-" . $this->Validate;

        if ($this->Unique) $css_class[] = "semiorbit-input-unique";

        if ($this->Type == DataType::DOUBLE || $this->Type == DataType::FLOAT) $css_class[] = "semiorbit-input-double";

        if ($this->Type == DataType::INT) $css_class[] = "semiorbit-input-int";

        if ($this->Control == Control::EDITOR) $css_class[] = "semiorbit-input-editor";

        if (!empty($this->Err)) $css_class[] = "semiorbit-val-err";

        $css_class = implode(" ", $css_class);

        return $css_class;

    }


    public function FormFieldName($form = null)
    {

        $form = Form::ID($form);


        $dataset = $this->ActiveDataSet() ? '[' . get_class($this->ActiveDataSet()) . ']' : null;

        //$new_with_auto_id = $this->ActiveDataSet()->IsNew() || $this->ActiveDataSet()->HasAutoID();

        //$dataset_id = $this->ActiveDataSet() ? $new_with_auto_id === true ? '[new]' : '[' . $this->ActiveDataSet()->ID['value'] . ']' : null;

        $field = '[' . $this->Name . ']';

        $field_id = null;

        $form_field_name = "{$form}{$dataset}{$field}{$field_id}";

        return $form_field_name;

    }

    public function DomInputID()
    {
        return $this->InputID ?: $this->InputName();
    }

    public function InputName($hash = true, $form = null, $suffix = '')
    {


        $input_name = $this->FormFieldName($form) . $suffix;

        //Log::Trace(0)->Inline()->Info("Not Hashed>>", $input_name);

        if ($hash) {

            if ( empty($suffix) ) {

                $input_name = $this->_HashedInputName ?:

                    $this->_HashedInputName = hash(Form::InputHashAlgo(), $input_name);

            } else {

                $input_name = hash(Form::InputHashAlgo(), $input_name);

            }

            //Log::Trace(0)->Inline()->Info("NAME>>>",  $input_name);

        }

        return $input_name;

    }

    public function InputValue($hash = true, $form = null, $method = null)
    {
        return Form::InputByName($this->InputName(true, $hash, $form), true, $form, $method);
    }

    public function DataValue()
    {
        if (is_empty($this->_DataValue)) $this->_DataValue = $this->Value;

        return $this->_DataValue;
    }

    public function SubmitInput($hash = true, $form = null)
    {

        if (!Form::IsSubmit($form)) return false;

        $this->_DataValue = $this->DataValue();

        $this->_InputValue = $this->InputValue($hash, $form);

        if ($this instanceof File || $this->Control == Control::FILE) {

            if (!empty($this->_InputValue) && (is_array($this->_InputValue) && !empty($this->_InputValue['name']))) {

                $this->SourceFile = $this->_InputValue;

                if (Uploader::IsAllowedFileType($this->SourceFile, $this->FileTypes)) {

                    $file_ext = is_array($this->SourceFile) ? Uploader::FileExt($this->SourceFile['name']) : Uploader::FileExt($this->SourceFile);

                    $this->Value = $this->TargetFileName ? $this->TargetFileName . $file_ext : $file_ext;
                }

            } else {

                $this->Value = $this->_DataValue;

            }

        } else {

            $this->Value = $this->_InputValue;

        }

        return true;

    }

    public function InputNameFormat($format_str = null)
    {
        if ($format_str !== null) $this->_InputNameFormat = $this->ValidateInputNameFormat($format_str);

        if (is_empty($this->_InputNameFormat))

            $this->_InputNameFormat = $this->ValidateInputNameFormat(CFG::$FormInputNameFormat);

        return $this->_InputNameFormat;
    }

    protected function ValidateInputNameFormat($format_str)
    {

        if (!stristr($format_str, ":name")) {
            $format_str .= ":name";
        }

        $format_str_clipped = str_ireplace(array(":model", ":name"), "", $format_str);

        if (!preg_match("/^[A-Za-z0-9_]+$/", $format_str_clipped)) {
            $format_str = ":model_:name";
        }

        return $format_str;

    }

    public function CheckRequiredValue()
    {

        if ($this->Required) {

            if (in_array($this->Type, array(DataType::CHAR, DataType::VARCHAR, DataType::TEXT, DataType::FILE,

                DataType::TIME, DataType::DATE, DataType::DATETIME, DataType::TIMESTAMP))) {

                return !is_empty($this->Value);

            } else {

                return $this->Value !== null;
            }

        }

        return true;

    }

    public function CheckUniqueValue()
    {
        if ($this->Unique) {

            $ds = $this->ActiveDataSet();

            if (!$ds) return true;

            $value = $ds->ActiveConnection()->Escape($this->Value);

            $id = $ds->ActiveConnection()->Escape($ds->ID->Value);

            /** @noinspection SqlNoDataSourceInspection */
            $sql = "SELECT {$this->Name} FROM {$ds->TableName()} WHERE {$this->Name} LIKE '{$value}' AND {$ds->ID->Name} != '{$id}' ";

            return (!$ds->ActiveConnection()->Find($sql));

        }

        return true;

    }

    public function ValidateValue()
    {

        if ($this->Value !== null && $this->Value !== '') {

            switch ($this->Validate) {

                case Validate::EMAIL :

                    return Validate::IsEmail($this->Value);
                    break;

                case Validate::URL :

                    return Validate::IsURL($this->Value);
                    break;

                case Validate::TEL :

                    return Validate::IsTel($this->Value);
                    break;

                case Validate::INT :

                    return is_int($this->Value);
                    break;

                case Validate::FLOAT :

                    return is_float($this->Value);
                    break;

                case Validate::DOUBLE :

                    return is_double($this->Value);
                    break;

                case Validate::DECIMAL :

                    return is_double($this->Value);
                    break;

                case Validate::NUMERIC :

                    return is_numeric($this->Value);
                    break;

                default :

                    return preg_match("/" . $this->Validate . "/i", $this->Value);
                    break;

            }

        }

        return true;

    }

    public function FilterValue($value = null)
    {

        if ($value === null) {

            if ($this->_FilterValue === $this->Value && $this->_FilteredValue) return $this->_FilteredValue;

            if ($value === '') return $value;

            $value = $this->Value;

            $this->_FilterValue = $value;

        }

        $filtered_value = $value;

        switch ($this->Validate) {

            case Validate::EMAIL :

                $filtered_value = filter_var($value, FILTER_SANITIZE_EMAIL);
                break;

            case Validate::URL :

                $filtered_value = filter_var($value, FILTER_SANITIZE_URL);
                break;

            case Validate::TEL :

                $filtered_value = filter_var($value, FILTER_SANITIZE_NUMBER_INT);
                break;

            case Validate::INT :

                $filtered_value = intval($value);
                break;

            case Validate::FLOAT :

                $filtered_value = floatval($value);
                break;

            case Validate::DOUBLE :

                $filtered_value = doubleval($value);
                break;

            case Validate::DECIMAL :

                $filtered_value = filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                break;

            case Validate::NUMERIC :

                $filtered_value = filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_SCIENTIFIC);
                break;

        }


        switch ($this->Type) {

            case DataType::CHAR :
            case DataType::VARCHAR :
            case DataType::TEXT :

                if (isset($this->AllowHtml) && $this->AllowHtml) $filtered_value = $value;
                else $filtered_value = filter_var($value, FILTER_SANITIZE_STRING);
                break;

            case DataType::BOOL :
            case DataType::INT :

                $filtered_value = intval($value);
                break;

            case DataType::DOUBLE :

                $filtered_value = doubleval($value);
                break;

            case DataType::FLOAT :

                $filtered_value = floatval($value);
                break;

            case DataType::DECIMAL :

                $filtered_value = filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                break;

        }

        if ($this->_FilterValue == $value) $this->_FilteredValue = $filtered_value;

        return $filtered_value;

    }


    public function LabelText()
    {
        if (is_empty($this->Caption)) $this->Caption = $this->Name;

        if (defined($this->Caption)) return constant($this->Caption);


        return $this->Trans($this->Caption);

    }

    public function Trans($key)
    {

        $class = $this->ActiveDataSet() && !(stristr($key, '.') || stristr($key, '::')) ?

            Str::ParamCase($this->ActiveDataSet()->Name()) . '.' : '';

        return Lang::Trans($class . $key);

    }

    public function RequiredMark($pms = [])
    {
        $html_output = $this->Required ? $this->ActiveTemplate()->RenderRequiredMark($this, false, $pms) : '';

        return $html_output;
    }

    public function ResetToDefault()
    {
        $this->setValue($this->DefaultValue);

        return $this;
    }

    /**
     * @return string
     */

    public function Html()
    {
        $this->_HtmlText = call_user_func_array($this->ActiveHtmlBuilder(), array($this));

        return $this->_HtmlText;
    }

    public function DefaultHtmlBuilder()
    {
        return strval($this->Value);
    }

    public function ActiveHtmlBuilder()
    {
        if ($this->_HtmlBuilder) return $this->_HtmlBuilder;

        else return function () {

            return $this->DefaultHtmlBuilder();

        };
    }

    /**
     * @param  $html_builder_func
     * @return $this
     */

    public function UseHtmlBuilder(callable $html_builder_func)
    {
        if ($html_builder_func) $this->_HtmlBuilder = $html_builder_func;

        return $this;
    }

    /**
     * @return TableViewCol Returns TableViewCol or FALSE if it is disabled
     */

    public function ActiveTableViewCol()
    {
        if ( ! $this->_TableViewCol ) $this->UseTableViewCol();

        return $this->_TableViewCol;
    }

    public function UseTableViewCol(TableViewCol $col = null)
    {

        $this->_TableViewCol = $col ? $col : $this->DefaultTableViewCol();

        $this->_TableViewCol->UseField($this);

        return $this;

    }

    public function DefaultTableViewCol()
    {
        return new TableViewCol($this);
    }

    public function __toString()
    {
        return $this->Html();
    }


    public function setName($value)
    {

        if ($this->ID == Str::PascalCase($this->Name)) $this->ID = null;

        if ($this->Caption == Str::PascalCase($this->Name)) $this->Name = null;

        $this->Name = strval($value);

        $this->InitializeProps();

        return $this;

    }

    public function setCaption($value)
    {
        $this->Caption = strval($value);
        return $this;
    }

    public function setControl($value)
    {
        $this->Control = $value;
        return $this;
    }

    public function setTag($value)
    {
        $this->Tag = strval($value);
        return $this;
    }

    public function setValue($value)
    {
        $this->Value = $value;
        return $this;
    }

    public function setType($value)
    {
        $this->Type = strval($value);
        return $this;
    }

    public function setRequired($value = true)
    {
        $this->Required = $value;
        return $this;
    } //TODO BOOLVAL

    public function setGroup($value)
    {
        $this->Group = strval($value);
        return $this;
    }

    public function setPermission($value)
    {
        $this->Permission = $value;
        return $this;
    }

    public function setTemplate($value)
    {
        $this->Template = strval($value);
        return $this;
    }

    public function setCssClass($value)
    {
        $this->CssClass = strval($value);
        return $this;
    }

    public function setControlCssClass($value)
    {
        $this->ControlCssClass = strval($value);
        return $this;
    }

    public function setInputCssClass($value)
    {
        $this->InputCssClass = strval($value);
        return $this;
    }

    public function setID($value)
    {
        $this->ID = strval($value);
        return $this;
    }

    public function setValidate($value)
    {
        $this->Validate = strval($value);
        return $this;
    }

    public function setUnique($value = true)
    {
        $this->Unique = ($value);
        return $this;
    }

    public function setDefaultValue($value)
    {
        $this->DefaultValue = $value;
        if ($this->Value === null) $this->Value = $value;
        return $this;
    }

    public function setNote($value)
    {
        $this->Note = strval($value);
        return $this;
    }

    public function setIsTitle($value = true)
    {
        $this->IsTitle = ($value);
        return $this;
    }

    public function setIsID($value = true)
    {
        $this->IsID = ($value);
        return $this;
    }

    public function setReadOnly($value = true)
    {
        $this->ReadOnly = ($value);
        return $this;
    }

    public function setInputIsReadOnly($value = true)
    {
        $this->InputIsReadOnly = ($value);
        return $this;
    }

    public function setView($value)
    {
        $this->View = strval($value);
        return $this;
    }

    public function setErr($key, $value)
    {
        $this->Err[$key] = $value;
        return $this;
    }

    public function setInputID($value)
    {
        $this->InputID = strval($value);
        return $this;
    }

    public function Hide()
    {

        $this->setControl(Control::NONE);

        $this->setView(FieldView::HIDDEN);

        return $this;

    }

    public function NoControl()
    {
        $this->setControl(Control::NONE);

        return $this;
    }

    public function HideColumn()
    {
        $this->ActiveTableViewCol()->Hide();

        return $this;
    }

    public function ShowColumn()
    {
        $this->ActiveTableViewCol()->Show();

        return $this;
    }

    public function SelectExpr($declare_table_name = true)
    {

        if ($this->_SelectExpr) return $this->_SelectExpr;

        if ($declare_table_name) {

            $table_name_prefix = ($declare_table_name !== true) ? $declare_table_name . '.' :

                ($this->ActiveDataSet() ? $this->ActiveDataSet()->TableName() . '.' : '');

        } else {

            $table_name_prefix = '';

        }

        return  $table_name_prefix . $this->Name;

    }

    public function setExcludeFromSelect($value = true)
    {
        $this->ExcludeFromSelect = $value;

        return $this;
    }

    public function Ignore()
    {
        $this->ExcludeFromSelect = true;

        $this->ReadOnly = true;

        return $this;
    }

    public function UseSelectExpr($select_expr)
    {
        $this->_SelectExpr = $select_expr;

        return $this;
    }

    public function WhereClausePrepareValue($value)
    {

        if ($this->_WhereClauseHelperFunc) {

            return call_user_func($this->_WhereClauseHelperFunc, $value);

        } else {

            return ( in_array($this->Type, array(DataType::INT, DataType::DECIMAL,

                                                 DataType::DECIMAL, DataType::FLOAT, DataType::BOOL) ) ) ?

                $value : "'{$value}'";

        }
    }

    public function WhereClauseHelper(callable $callable)
    {
        $this->_WhereClauseHelperFunc = $callable;

        return $this;
    }

    public function PreparedValue()
    {
        return $this->WhereClausePrepareValue($this->Value);
    }

    public function setColCssClass($css_class)
    {
        $this->ActiveTableViewCol()->CssClass = $css_class;

        return $this;
    }


}
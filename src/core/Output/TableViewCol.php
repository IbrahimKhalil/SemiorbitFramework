<?php
/*
*------------------------------------------------------------------------------------------------
* SEMIORBIT - TABLE VIEW COLUMN					 					 semiorbit.com
*------------------------------------------------------------------------------------------------
*/

namespace Semiorbit\Output;


use Semiorbit\Field\FieldView;
use Semiorbit\Support\AltaArray;
use Semiorbit\Field\Field;

/**
 * Class TableViewCol
 * @package Semiorbit\Output
 */
class TableViewCol extends AltaArray
{


    public $ID;

    protected $_Field;

    public $Caption;

    public $Link;

    public $Control = TableViewControl::TEXT;

    public $Target;

    public $CssClass;

    public $LinkCssClass;

    protected $_HtmlText;

    protected $_HtmlBuilder;

    protected $_Hidden = null;



    public function __construct(Field $field)
    {

        parent::__construct( array() );

        $this->UseField($field);

    }

    public static function Create(Field $field)
    {
        return new TableViewCol($field);
    }

    public function UseField(Field $field)
    {

        $this->ID = $field->DomInputID();

        $this->_Field = $field;

        //$this->Caption = $this->HeaderText();

        $this->CssClass = $field->CssClass;

        return $this;

    }

    /**
     * @return Field
     */

    public function ActiveField()
    {
        return $this->_Field;
    }

    public function HeaderText()
    {
        return ( ! is_empty( $this->Caption ) ) ? trans( $this->Caption ) :

            ( ( $this->ActiveField() instanceof Field ) ? $this->ActiveField()->LabelText() : $this->ID );
    }

    /**
     * @return string
     */

    public function Html()
    {
        $this->_HtmlText = call_user_func($this->ActiveHtmlBuilder(), $this->ActiveField());

        return $this->_HtmlText;
    }

    public function DefaultHtmlBuilder()
    {
        return $this->ActiveField()->Html();
    }

    public function ActiveHtmlBuilder()
    {
        if ($this->_HtmlBuilder) return $this->_HtmlBuilder;

        else return function () {

            return $this->DefaultHtmlBuilder();

        };
    }

    /**
     * @param \Closure $html_builder_func
     * @return $this
     */

    public function UseHtmlBuilder(\Closure $html_builder_func)
    {
        if ($html_builder_func) $this->_HtmlBuilder = $html_builder_func;

        return $this;
    }

    public function __toString()
    {
        return $this->Html();
    }

    public function Hide()
    {
        $this->_Hidden = true;

        return $this;
    }

    public function Show()
    {
        $this->_Hidden = false;

        return $this;
    }

    /**
     * Returns whether column is set to visible or hidden, but if it is not explicitly set (null) will be returned
     *
     * @return bool|null
     */

    public function IsVisible()
    {
        if ( $this->ActiveField() instanceof Field && $this->ActiveField()->View == FieldView::HIDDEN ) return false;

        return ($this->_Hidden === null) ? null : ! $this->_Hidden;
    }



    public function setID($id) { $this->ID = $id; return $this; }

    public function setField(Field $field) { $this->_Field = $field; return $this; }

    public function setCaption($caption) { $this->Caption = $caption; return $this; }

    public function setTarget($target) { $this->Target = $target; return $this; }

    public function setCssClass($css_class) { $this->CssClass = $css_class; return $this; }

    public function setLinkCssClass($css_class) { $this->LinkCssClass = $css_class; return $this; }

    public function setControl($control = TableViewControl::TEXT) { $this->Control = $control; return $this; }


}
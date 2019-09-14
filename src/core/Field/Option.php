<?php
/*
 *----------------------------------------------------------------------------------------------
* DATATYPE - SEMIORBIT FIELD "DATA TYPE" HELPER    	  						    semiorbit.com
*-----------------------------------------------------------------------------------------------
*
*/

namespace Semiorbit\Field;






class Option
{

    const Value = 'Value';

    const Key = 'Key';

    const AtBottom = 'AtBottom';
	
	public $Value;

    public $Key;

    public $AtBottom;

    public $EnableTextFormatting = true;

    public $Disabled;


    public function __construct($key, $value, $disabled = false)
    {

        $this->setKey($key);

        $this->setValue($value);

        $this->setDisabled($disabled);

    }

    public static function set($param1, $param2 = null, $disabled = false)
    {
        return $param2 != null ? new Option($param1, $param2, $disabled) : new Option(null, $param1, $disabled);
    }


    public function setAtBottom($val = true)
    {
        $this->AtBottom = $val;

        return $this;
    }

    public function setKey($key)
    {
        $this->Key = $key;

        return $this;
    }

    public function setValue($value)
    {
        $this->Value = $value;

        return $this;
    }

    public function setDisabled($value = true)
    {
        $this->Disabled = $value;

        return $this;
    }

    public function __toString()
    {
        return strval( $this->Value );
    }

    public function DisableTextFormatting()
    {
        $this->EnableTextFormatting = false;

        return $this;
    }

	
}
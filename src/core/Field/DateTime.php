<?php
/*
*-----------------------------------------------------------------------------------------------
* FIELD - SEMIORBIT FIELD                    			  						    semiorbit.com
*-----------------------------------------------------------------------------------------------
* 
*/

namespace Semiorbit\Field;





/**
 * Class DateTime
 * @package Semiorbit\Field
 *
 * @method DateTime  setProps($array)
 * @method DateTime  UseTemplate($form_template = null)
 * @method DateTime  UseDataSet(\Semiorbit\Data\DataSet $dataset)
 * @method DateTime  UseHtmlBuilder(callable $html_builder_func)
 * @method DateTime  ResetToDefault()
 * @method DateTime  setName($value)
 * @method DateTime  setCaption($value)
 * @method DateTime  setControl($value)
 * @method DateTime  setTag($value)
 * @method DateTime  setValue($value)
 * @method DateTime  setType($value)
 * @method DateTime  setRequired($value)
 * @method DateTime  setGroup($value)
 * @method DateTime  setPermission($value)
 * @method DateTime  setTemplate($value)
 * @method DateTime  setCssClass($value)
 * @method DateTime  setID($value)
 * @method DateTime  setValidate($value)
 * @method DateTime  setUnique($value)
 * @method DateTime  setDefaultValue($value)
 * @method DateTime  setNote($value)
 * @method DateTime  setIsTitle($value)
 * @method DateTime  setIsID($value)
 * @method DateTime  setReadOnly($value = true)
 * @method DateTime  setView($value)
 * @method DateTime  setErr($key, $value)
 * @method DateTime  NoControl()
 * @method DateTime  Hide()
 * @method DateTime  UseTableViewCol(\Semiorbit\Output\TableView $col = null)
 * @method DateTime  HideColumn()
 * @method DateTime  ShowColumn()
 * @method DateTime  setControlCssClass($value)
 */
class DateTime extends Field
{

    public $Control = Control::DATETIME;

    public $Type = DataType::TIMESTAMP;

    public $Format = '%Y-%m-%d %H:%M:%S';

    public $ShowTime = true;

    private static $_CalendarLoaded = false;


    public function PreRender()
    {

        if (is_empty($this->Control)) $this->Control = Control::TEXT;

        if (is_empty($this->Type)) $this->Type = DataType::VARCHAR;

        if (is_empty($this->Format)) $this->Format = '%Y-%m-%d %H:%M:%S';

        if (is_empty($this->ShowTime)) $this->ShowTime = false;

        $this->setControlCssClass(($this->ShowTime ? 'datetime-input ' : 'date-input ') . $this->ControlCssClass);

    }


    public static function CalendarLoaded()
    {
        return self::$_CalendarLoaded;
    }


    public static function MarkCalendarLoaded($loaded = true)
    {
        self::$_CalendarLoaded = $loaded;
    }

    /**
     * @param $value
     * @return DateTime
     */

    public function setFormat($value)
    {
        $this->Format = strval($value);

        return $this;
    }

    /**
     * @param $value
     * @return DateTime
     */

    public function setShowTime($value)
    {
        $this->ShowTime = ($value);

        return $this;
    }


}

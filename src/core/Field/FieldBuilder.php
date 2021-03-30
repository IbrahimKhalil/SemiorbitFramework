<?php
/*
*-----------------------------------------------------------------------------------------------
* FIELD BUILDER       								   						    semiorbit.com
*-----------------------------------------------------------------------------------------------
*
*/

namespace Semiorbit\Field;





trait FieldBuilder
{

    public static function Create($field)
    {

        if ($field instanceof Field) {

            if (get_class($field) == 'Semiorbit\\Field\\' . $field['control']) return $field;

        }

        $control = $field['control'];

        if (!is_empty($control)) {

            if (method_exists('Field', $control)) {

                $field_obj = call_user_func_array(array('Field', $control), array($field));

                if ($field instanceof Field) {

                    if ($field->ActiveDataSet()) $field_obj->UseDataSet($field->ActiveDataSet());

                    $field_obj->UseTemplate($field->ActiveTemplate());

                    return $field_obj;

                }

            }

        }


        return new Field($field);

    }

    /**
     * @param $field
     * @return \Semiorbit\Field\ID
     */

    public static function ID($field)
    {
        return new ID($field);
    }

    /**
     * @param $field
     * @return \Semiorbit\Field\Hidden
     */

    public static function Hidden($field)
    {
        return new Hidden($field);
    }

    /**
     * @param $field
     * @return \Semiorbit\Field\Text
     */

    public static function Text($field)
    {
        return new Text($field);
    }

    /**
     * @param $field
     * @return \Semiorbit\Field\Password
     */

    public static function Password($field)
    {
        return new Password($field);
    }

    /**
     * @param $field
     * @return \Semiorbit\Field\TextArea
     */

    public static function TextArea($field)
    {
        return new TextArea($field);
    }

    /**
     * @param $field
     * @return \Semiorbit\Field\Editor
     */

    public static function Editor($field)
    {
        return new Editor($field);
    }

    /**
     * @param $field
     * @return \Semiorbit\Field\DateTime
     */

    public static function DateTime($field)
    {
        return new DateTime($field);
    }

    /**
     * @param $field
     * @return \Semiorbit\Field\Select
     */

    public static function Select($field)
    {
        return new Select($field);
    }

    /**
     * @param $field
     * @return \Semiorbit\Field\Checkbox
     */

    public static function Checkbox($field)
    {
        return new Checkbox($field);
    }

    /**
     * @param $field
     * @return \Semiorbit\Field\Custom
     */

    public static function Custom($field)
    {
        return new Custom($field);
    }


    /**
     * @param $field
     * @return \Semiorbit\Field\File
     */

    public static function File($field)
    {
        return new File($field);
    }


    /**
     * @param $field
     * @return \Semiorbit\Field\Number
     */

    public static function Number($field)
    {
        return new Number($field);
    }

}

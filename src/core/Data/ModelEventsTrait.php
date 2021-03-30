<?php

namespace Semiorbit\Data;


use Semiorbit\Field\File;
use Semiorbit\Form\Form;


trait ModelEventsTrait {


    public function onStart()
    {
        return;
    }

    public function onBeforeInsert()
    {

    }

    public function onInsert($res)
    {
        return $res;
    }

    public function onBeforeUpdate()
    {

    }

    public function onUpdate($res)
    {
        return $res;
    }

    public function onBeforeRemove()
    {
        return null;
    }

    public function onRemove($res)
    {
        return $res;
    }

    public function onBeforeSave()
    {
        return null;
    }

    public function onSave($res)
    {
        return $res;
    }

    public function onUserInsertedRow($res, &$show_form, $show_err_report = false, $form_output = null)
    {

        /** @var DataSet $this */

        echo Msg::Show($res);

        if ($show_err_report) Form::ActiveTemplate()->ListErrReport($this->ErrorList());

        $show_form = true;

        return $form_output;

    }

    public function onUserUpdatedRow($res, &$show_form, $show_err_report = false, $form_output = null)
    {

        /** @var DataSet $this */

        echo Msg::Show($res);

        if ($show_err_report) Form::ActiveTemplate()->ListErrReport($this->ErrorList());

        $show_form = true;

        return $form_output;

    }

    public function onUserRemovedRow($res)
    {
        if ($res === true || $res === null || $res === Msg::DBOK)

            $res = Msg::ROW_DELETED;

        echo Msg::Show($res);
    }

    public function onUpload($path)
    {
        return $path;
    }

    public function onResize($size,$path)
    {
        return $size . $path;
    }

    public function onRenderStart()
    {
        return;
    }

    public function onRenderComplete()
    {
        return;
    }

    public function onRenderControlStart($field)
    {
        return $field;
    }

    public function onRenderControlComplete($field)
    {
        return $field;
    }

    public function onUserRemovedFile(File $file)
    {
        return;
    }

}

interface NextForm
{

    const NO_FORM = 0;

    const EDIT_ROW = 1;

    const NEW_ROW = 2;

    const GOTO_NEXT_ROW_ID = 3;

    const NEXT_ROW = 4;

    const PREV_ROW = 5;

}

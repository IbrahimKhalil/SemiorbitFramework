<?php
/*
*-----------------------------------------------------------------------------------------------
* FIELD - SEMIORBIT FIELD                    			  						    semiorbit.com
*-----------------------------------------------------------------------------------------------
* 
*/

namespace Semiorbit\Form;




use Semiorbit\Component\Finder;
use Semiorbit\Config\Config;
use Semiorbit\Field\File;
use Semiorbit\Output\Render;
use Semiorbit\Field\Field;


class FormTemplate
{

    const BASE_TEMPLATE = 'form';


    protected $_TemplateName;

    protected $_Settings = array();

    protected $_SelectedTemplates = array();


    public function __construct($form_template)
    {
        $this->_TemplateName = $form_template;
    }

    public function Name()
    {
        return $this->_TemplateName;
    }

    public function SelectedTemplates()
    {
        if (! $this->_SelectedTemplates) {

            if ( $this->Name() != Config::DefaultFormTemplate() ) $this->_SelectedTemplates[] = $this->Name();

            if ( Config::DefaultFormTemplate() != static::BASE_TEMPLATE ) $this->_SelectedTemplates[] = Config::DefaultFormTemplate();

            $this->_SelectedTemplates[] = static::BASE_TEMPLATE;

        }

        return $this->_SelectedTemplates;
    }

    public function TemplateSelector($view)
    {

        $arr = array();

        foreach ($this->SelectedTemplates() as $template) {

            $arr[] = $template . "/{$view}";

        }

        return $arr;

    }


    public function OpenForm($id, $flush_output = true, $pms = [])
    {
        return Render::OpenBox('', $this->TemplateSelector('form'), $id, $flush_output, $pms);
    }

    public function CloseForm($flush_output = true, $pms = [])
    {
        return Render::CloseBox( $this->TemplateSelector('form'), $flush_output, $pms);
    }

    public function OpenGroup($group, $flush_output = true)
    {

        $group_props['group'] = $group;

        return Render::OpenBox( isset($group['caption']) ? $group['caption'] : "",

            $this->TemplateSelector('group'), isset($group['id']) ? $group['id'] : "",

            $flush_output, $group_props );

    }

    public function CloseGroup($group, $flush_output = true)
    {
        return Render::CloseBox( $this->TemplateSelector('group')
            
            , $flush_output, array('group' => $group));
        
    }

    public function RenderSubmit($flush_output = true, $pms = array())
    {
        return Render::Widget( $this->TemplateSelector('submit'),

            $pms, $flush_output);
    }

    public function RenderVerification($flush_output = true, $pms = array())
    {
        return Render::Widget( $this->TemplateSelector('verification'),

            $pms, $flush_output);
    }

    public function RenderControl(Field $field, $flush_output = true, $pms = [])
    {
        $pms['field'] = $field;

        return Render::Widget( $this->TemplateSelector('controls/' . $field->Control), $pms, $flush_output);
    }

    public function RenderRequiredMark(Field $field, $flush_output = true, $pms = [])
    {
        $pms['field'] = $field;

        return Render::Widget( $this->TemplateSelector('required-mark'),

            $pms, $flush_output);
    }


    public function OpenField(Field $field, $id = "", $flush_output = false, $pms = [])
    {
        $pms['field'] = $field;

        return Render::OpenBox('', $this->TemplateSelector('field'),

            $id, $flush_output, $pms);
    }

    public function CloseField(Field $field, $flush_output = false, $pms = [])
    {
        $pms['field'] = $field;

        return Render::CloseBox( $this->TemplateSelector('field'),

            $flush_output, $pms);
    }

    public function OpenLabel(Field $field, $id = "", $flush_output = false, $pms = [])
    {
        $pms['field'] = $field;

        return Render::OpenBox( '', $this->TemplateSelector('label'),

            $id, $flush_output, $pms );
    }

    public function CloseLabel(Field $field, $flush_output = false, $pms = [])
    {
        $pms['field'] = $field;

        return Render::CloseBox( $this->TemplateSelector('label'),

            $flush_output, $pms );
    }

    public function OpenInput(Field $field, $id = "", $flush_output = false, $pms = [])
    {
        $pms['field'] = $field;

        return Render::OpenBox( '', $this->TemplateSelector('input'),

            $id, $flush_output, $pms );
    }

    public function CloseInput(Field $field, $flush_output = false, $pms = [])
    {
        $pms['field'] = $field;

        return Render::CloseBox( $this->TemplateSelector('input'),

            $flush_output, $pms);
    }

    public function OpenNote(Field $field, $id = "", $flush_output = false, $pms = [])
    {
        $pms['field'] = $field;

        return Render::OpenBox('', $this->TemplateSelector('note'),

            $id, $flush_output, $pms);
    }

    public function CloseNote(Field $field, $flush_output = false, $pms = [])
    {
        $pms['field'] = $field;

        return Render::CloseBox( $this->TemplateSelector('note'),

            $flush_output, $pms);
    }

    public function ListErrReport(array $err_list, $flush_output = true)
    {
        $pms['err_list'] = $err_list;

        return Render::Widget( $this->TemplateSelector('list-err-report'),

            $pms, $flush_output);
    }


    public function FilePreview(File $field, $flush_output = false)
    {
        $pms['field'] = $field;

        return Render::Widget( $this->TemplateSelector('file-preview'),

            $pms, $flush_output);
    }


    public function Widget($widget_name, $pms = array(), $flush_output = false)
    {

        return Render::Widget( $this->TemplateSelector($widget_name),

            $pms, $flush_output);
    }


    public function SettingsPath()
    {

        $template = $this->Name();

        $settings_path = Render::CacheViewPath( Config::ViewsDir() . '@Settings_' . $template) ?: call_user_func( function () use ( $template ) {

            $path = Finder::LookFor($template . '/settings.inc', Finder::Views, true);

            Render::CacheViewPath( Config::ViewsDir() . '@Settings_' . $template, $path);

            return $path;

        });

        return $settings_path ? $settings_path['path'] : false;

    }

    public function LoadSettings()
    {
        if ( empty( $this->_Settings ) ) {

            $path = $this->SettingsPath();

            if ($path)

                /** @noinspection PhpIncludeInspection */
                $this->_Settings = include "{$path}";

        }

        return $this->_Settings;

    }

    public function Settings($key = null)
    {
        $this->LoadSettings();

        return ( isset( $this->_Settings[$key] ) ) ? $this->_Settings[$key] : null;
    }

    public function setSettings($key, $value)
    {
        $this->LoadSettings();

        $this->_Settings[$key] = $value;
    }



}
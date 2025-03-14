<?php
/*
*-----------------------------------------------------------------------------------------------
* FIELD - SEMIORBIT FIELD                    			  						    semiorbit.com
*-----------------------------------------------------------------------------------------------
* 
*/

namespace Semiorbit\Field;





use Semiorbit\Config\Config;
use Semiorbit\Debug\FileLog;
use Semiorbit\Support\Base64Resources;
use Semiorbit\Support\Path;

/**
 * Class Editor
 * @package Semiorbit\Field
 *
 * @method Editor  setProps($array)
 * @method Editor  UseTemplate($form_template = null)
 * @method Editor  UseDataSet(\Semiorbit\Data\DataSet $dataset)
 * @method Editor  ResetToDefault()
 * @method Editor  setName($value)
 * @method Editor  setCaption($value)
 * @method Editor  setControl($value)
 * @method Editor  setTag($value)
 * @method Editor  setValue($value)
 * @method Editor  setType($value)
 * @method Editor  setRequired($value)
 * @method Editor  setGroup($value)
 * @method Editor  setPermission($roles, $permissions = null)
 * @method Editor  setTemplate($value)
 * @method Editor  setCssClass($value)
 * @method Editor  setID($value)
 * @method Editor  setValidate($value)
 * @method Editor  setUnique($value)
 * @method Editor  setDefaultValue($value)
 * @method Editor  setNote($value)
 * @method Editor  setIsTitle($value)
 * @method Editor  setIsID($value)
 * @method Editor  setReadOnly($value = true)
 * @method Editor  setView($value)
 * @method Editor  setErr($key, $value)
 * @method Editor  NoControl()
 * @method Editor  Hide()
 * @method Editor  UseTableViewCol(\Semiorbit\Output\TableView $col = null)
 * @method Editor  HideColumn()
 * @method Editor  ShowColumn()
 * @method Editor  setControlCssClass($value)
 */

class Editor extends Field
{

    public $Control = Control::EDITOR;

    public $Type = DataType::TEXT;

    public $MaxLength;

    public $AllowHtml = true;

    public $ResourcesSubDirectory;

    public $Resources = [];

    public $ContentLanguage = LANG;


    public function PreRender()
    {

        if (is_empty($this->Control)) $this->Control = Control::EDITOR;

        if (is_empty($this->Type)) $this->Type = DataType::TEXT;

        if (is_empty($this->MaxLength)) $this->MaxLength = ($this->Type == DataType::VARCHAR) ? 500 : 65535;

        if (is_empty($this->ContentLanguage)) $this->ContentLanguage = LANG;

    }


    public function InputAttrs(array $include_attrs = [], array $exclude_attrs = [])
    {

        $attrs = array(Field::MAX_LENGTH);

        $include_attrs = array_merge($attrs, $include_attrs);

        return $this->BaseAttrs($include_attrs, $exclude_attrs);
    }

    public function DefaultHtmlBuilder()
    {
        return $this->AllowHtml ?  ( $this->Value ?: '' ) : nl2br( htmlentities( $this->Value ) );
    }

    /**
     * @param $value
     * @return Editor
     */

    public function setMaxLength($value)
    {
        $this->MaxLength = strval($value);

        return $this;
    }

    public function setResourcesSubDirectory($dir)
    {
        $this->ResourcesSubDirectory = $dir;

        return $this;
    }


    public function ValidateValue()
    {

        if ($this->AllowHtml && $this->ResourcesSubDirectory) {

            $data = Base64Resources::ExtractFromHtml(

                $this->Value,

                $this->ActiveDataSet()->DocumentsURL() . $this->ResourcesSubDirectory);

            $this->Value = $data[0];

            $this->Resources = $data[1];

        }

        return parent::ValidateValue();

    }


    public function SaveResources(): int
    {

        $saved = 0;

        foreach ($this->Resources as $resource) {

            [$img_data, $img_fn] = $resource;

            $res = file_put_contents(

                $this->DirPath(true, true) . $img_fn,

                base64_decode($img_data));

            if ($res) $saved++;

            else {

                if (Config::DebugMode()) {

                    FileLog::Debug("FWK910", "FWK@SAVE_RES_EDITOR", $img_fn);

                }

            }

        }

        return $saved;

    }


    public function DirPath($real_path = true, $create = false)
    {

        $path = ( $this->ActiveDataSet() ) ?

            ( $real_path ? $this->ActiveDataSet()->DocumentsRealPath() : $this->ActiveDataSet()->DocumentsPath() )

            : ( Config::DocumentsPath($real_path) );

        $path .= $this->ResourcesSubDirectory;

        if ( $real_path && $create && ! file_exists( $path ) ) mkdir($path, 0777, true);



        return Path::Normalize($path);

    }


    public function setContentLanguage($lang = LANG)
    {

        $this->ContentLanguage = $lang;

        return $this;

    }


}

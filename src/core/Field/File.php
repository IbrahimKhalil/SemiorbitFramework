<?php
/*
*-----------------------------------------------------------------------------------------------
* FIELD - SEMIORBIT FIELD                    			  						    semiorbit.com
*-----------------------------------------------------------------------------------------------
* 
*/

namespace Semiorbit\Field;


use Semiorbit\Config\Config;
use Semiorbit\Data\Msg;
use Semiorbit\Output\TableViewCol;
use Semiorbit\Session\Session;
use Semiorbit\Support\Path;
use Semiorbit\Support\Uploader;


/**
 * Class File
 * @package Semiorbit\Field
 *
 * @method File  setProps($array)
 * @method File  UseTemplate($form_template = null)
 * @method File  UseDataSet(\Semiorbit\Data\DataSet $dataset)
 * @method File  ResetToDefault()
 * @method File  setName($value)
 * @method File  setCaption($value)
 * @method File  setControl($value)
 * @method File  setTag($value)
 * @method File  setValue($value)
 * @method File  setType($value)
 * @method File  setRequired($value = true)
 * @method File  setGroup($value)
 * @method File  setPermission($value)
 * @method File  setTemplate($value)
 * @method File  setCssClass($value)
 * @method File  setID($value)
 * @method File  setValidate($value)
 * @method File  setUnique($value)
 * @method File  setDefaultValue($value)
 * @method File  setNote($value)
 * @method File  setIsTitle($value)
 * @method File  setIsID($value)
 * @method File  setReadOnly($value = true)
 * @method File  setView($value)
 * @method File  setErr($key, $value)
 * @method File  NoControl()
 * @method File  Hide()
 * @method File  UseTableViewCol(\Semiorbit\Output\TableViewCol $col = null)
 * @method File  HideColumn()
 * @method File  ShowColumn()
 * @method File  setControlCssClass($value)
 */

class File extends Field
{

    public $Control = Control::FILE;

    public $Type = DataType::FILE;

    public $MaxSize;

    public $TargetFileName;

    public $FileTypes = 'jpg|png|gif|jpeg|svg';

    public $SubDirectory;

    public $AutoResize;

    public $SourceFile;

    public $Thumbnails = array();

    public $View = FieldView::IMAGE;

    public $TableViewColThumbnail;

    public $TableViewColView = FieldView::IMAGE;

    public $CleanDataBeforeDeleteFile = true;

    const REMOVE_FILE_KEY = 'f86257bd9ce35726333e90ede8d34ac6';
    
    const REMOVE_FILE_KEY_TM = File::REMOVE_FILE_KEY . 'tm';

    const TRACKER_PREFIX = 'tracker::';

    public function PreRender()
    {

        if (is_empty($this->Control)) $this->Control = Control::FILE;

        if (is_empty($this->Type)) $this->Type = DataType::FILE;

        if (is_empty($this->View)) $this->View = FieldView::IMAGE;

        if ( isset( $_GET[File::REMOVE_FILE_KEY] )  && isset($_GET[File::REMOVE_FILE_KEY_TM])) {

            if ( isset($_COOKIE[$this->TrackerName() . $_GET[File::REMOVE_FILE_KEY_TM]])

                && ($_GET[File::REMOVE_FILE_KEY] == $_COOKIE[$this->TrackerName()  . $_GET[File::REMOVE_FILE_KEY_TM]])) {
                

                $allow_delete = true;

                if ($this->CleanDataBeforeDeleteFile && $this->ActiveDataSet()) {

                    $allow_delete = ($this->ExcludeFromSelect) ? true :

                        $this->ActiveDataSet()->ActiveConnection()

                        ->Cmd("UPDATE {$this->ActiveDataSet()->TableName()} SET {$this->Name} = NULL

                                WHERE {$this->ActiveDataSet()->ID->Name} = '{$this->ActiveDataSet()->ID->Value}'");

                }

                
                if ($allow_delete) {

                    $this->DeleteFiles(true, true);

                    $this->Value = null;

                    Session::Clear($this->TrackerName());

                    if ($this->ActiveDataSet()) $this->ActiveDataSet()->onUserRemovedFile($this);

                }


                $_GET[File::REMOVE_FILE_KEY] = '';

                unset( $_GET[File::REMOVE_FILE_KEY] );

                $_GET[File::REMOVE_FILE_KEY_TM] = '';

                unset( $_GET[File::REMOVE_FILE_KEY_TM] );

            }


        }

    }

    public function TrackCode($tm  = '')
    {

        $track_code = md5(uniqid($this->TrackerName(), true));

        setcookie($this->TrackerName() . $tm, $track_code, time() + 3600);

        return $track_code;

    }

    public function TrackerName()
    {
        return static::TRACKER_PREFIX . $this->InputName();
    }


    public function InputAttrs(array $include_attrs = [], array $exclude_attrs = [])
    {

        $attrs = array();

        $include_attrs = array_merge($attrs, $include_attrs);

        return $this->BaseAttrs($include_attrs, $exclude_attrs);

    }

    /**
     * @param $name
     * @param $width
     * @param $height
     * @return File
     */

    public function setThumbnail($name, $width, $height)
    {
        $this->Thumbnails[$name] = array('w'=>$width, 'h'=>$height);

        return $this;
    }

    public function DetachThumbnail($name)
    {
        unset( $this->Thumbnails[$name] );

        return $this;
    }

    public function DetachThumbnails()
    {
        $this->Thumbnails = array();

        return $this;
    }

    public function DirName($thumbnail = null)
    {


        $dir_name = Path::Normalize( $this->SubDirectory );

        if ( $thumbnail ) $dir_name = $dir_name . $thumbnail;

        return  trim( $dir_name, '/' );

    }

    public function DirPath($thumbnail = null, $real_path = true, $create = false)
    {

        $path = ( $this->ActiveDataSet() ) ?

                ( $real_path ? $this->ActiveDataSet()->DocumentsRealPath() : $this->ActiveDataSet()->DocumentsPath() )

                : ( Config::DocumentsPath($real_path) );

        $path .= $this->DirName();

        if ( $real_path && $create && ! file_exists( $path ) ) mkdir($path, 0777, true);



        if ( $thumbnail ) {

            $path = $path . '/' . $thumbnail;

            if ( $real_path && $create && ! file_exists( $path ) ) mkdir($path, 0777, true);

        }

        return Path::Normalize($path);

    }



    public function FileName($old_data_value = false)
    {

        $value_parts = $this->ValueParts($old_data_value);

        return ( count($value_parts)  > 1 ) ? $value_parts[0] : ( $this->ActiveDataSet() ? $this->ActiveDataSet()->ID->Value :

            ( $this->TargetFileName ?: ( $this->TargetFileName = uniqid() ) ) );

    }

    public function FileExt($old_data_value = false)
    {

        $value = $old_data_value ? $this->DataValue() : ($this->Value ?? '');

        if( ! strstr( $value, '.' ) ) return $value;

        $value_parts = $this->ValueParts($old_data_value);

        if ( empty( $value_parts ) ) return '';

        return ( count($value_parts)  > 1 ) ? $value_parts[1] : $value_parts[0];

    }

    protected function ValueParts($old_data_value = false)
    {
        return explode( '.', $old_data_value ? $this->DataValue() : ($this->Value ?? '') );
    }

    public function BaseName($old_data_value = false)
    {
        return $this->FileName($old_data_value) . '.' . $this->FileExt($old_data_value);
    }

    public function Path($thumbnail = null, $old_data_value = false)
    {

        # eg. 'documents/posts/small/{id}.png';

        $fp = $this->DirPath( $thumbnail, false ) . $this->BaseName($old_data_value);

        return $fp;

    }

    public function RealPath($thumbnail = null, $old_data_value = false)
    {

        # eg. '/PUBLIC/documents/posts/small/{id}.png';

        $fp = $this->DirPath($thumbnail) . $this->BaseName($old_data_value);

        return $fp;

    }

    public function URL($thumbnail = null, $add_file_time = true)
    {

        $documents_url = $this->ActiveDataSet() ? $this->ActiveDataSet()->DocumentsURL() : Config::DocumentsURL();

        $relative_dir_path = $this->DirName($thumbnail);

        if ( ! empty( $relative_dir_path ) ) $relative_dir_path .= '/';

        $file_time_suffix = $add_file_time ? '?' . $this->FileTime($thumbnail) : '';

        return $documents_url . $relative_dir_path . $this->BaseName() . $file_time_suffix;

    }

    public function Upload()
    {

        if ( is_empty( $this->SourceFile ) || ( is_array( $this->SourceFile ) && is_empty( $this->SourceFile['name'] ) ) )

            return Msg::UPLOAD_OK;

        if ( ! Uploader::IsAllowedFileType( $this->SourceFile, $this->FileTypes ) ) return Msg::FILE_TYPE_ERR;

        if ( $this->MaxSize && is_array($this->SourceFile) ) {

            if ( ! isset($this->SourceFile['size']) ) return Msg::UPLOAD_FAILED;

            if ( $this->SourceFile['size'] > ($this->MaxSize * 1024) ) return Msg::UPLOAD_FAILED;

        }

        $this->DeleteFiles(true, true);

        $upload_res = Uploader::Upload( $this->SourceFile, $this->DirPath( null, true, true ), $this->FileName() );

        if ( $upload_res == Msg::UPLOAD_OK ) {

            $file_ext = is_array( $this->SourceFile ) ? Uploader::FileExt( $this->SourceFile['name'] ) : Uploader::FileExt( $this->SourceFile );

            $this->Value = $this->TargetFileName ? $this->TargetFileName . $file_ext : $file_ext;

            if (! $this->CreateThumbnails()) return  Msg::RESIZE_FAILED;
            

            if ( $this->ActiveDataSet() ) $this->ActiveDataSet()->onUpload( $this->RealPath() );

        } else {

            $this->Value = null;

            $this->_InputValue = null;

        }

        return $upload_res;

    }
    
    public function CreateThumbnails() : bool
    {

        if (  $this->AutoResize && in_array( $this->FileExt(), array( "jpg", "jpeg", "png", "gif", "bmp") ) ) {


            foreach ($this->Thumbnails as $thumbnail => $thumbnail_details) {

                $create_thumbnail = Uploader::CreateThumbnail($this->RealPath(), $this->DirPath($thumbnail, true, true),

                    $this->FileName(), $thumbnail_details['w'], $thumbnail_details['h']);

                if (!$create_thumbnail) return false;

                if ($this->ActiveDataSet()) $this->ActiveDataSet()->onResize($thumbnail, $this->RealPath($thumbnail));

            }

            return true;

        }
        
        return true;
        
    }

    public function Rename($current_id, $new_id)
    {

        $current_fn = $this->DirPath( null, true, false ) . $current_id . '.' . $this->FileExt();

        $new_fn = $this->DirPath( null, true, false ) . $new_id . '.' . $this->FileExt();

        if (file_exists($current_fn) && !file_exists($new_fn)) rename($current_fn, $new_fn);

        foreach ($this->Thumbnails as $thumbnail => $thumbnail_details) {

            $current_fn = $this->DirPath( $thumbnail, true, false ) . $current_id . '.' . $this->FileExt();

            $new_fn = $this->DirPath( $thumbnail, true, false ) . $new_id . '.' . $this->FileExt();

            if (file_exists($current_fn) && !file_exists($new_fn)) rename($current_fn, $new_fn);

        }

    }

    public function FileExists($thumbnail = null, $old_data_value = false)
    {
        $file =  $this->RealPath($thumbnail, $old_data_value);

        return file_exists($file);
    }
    
    public function DeleteFile($thumbnail = null, $old_data_value = false)
    {
        $file =  $this->RealPath($thumbnail, $old_data_value);

        if ( file_exists($file) ) unlink($file);
    }

    public function DeleteThumbnails($old_data_value = false)
    {
        foreach ( $this->Thumbnails as $thumbnail => $thumbnail_details ) {

            $this->DeleteFile( $thumbnail, $old_data_value );

        }
    }

    public function DeleteFiles($old_data_value = false, $current_data_value = false)
    {
        $this->DeleteFile(null, $old_data_value);

        $this->DeleteThumbnails($old_data_value);

        if( $current_data_value ) {

            $this->DeleteFile();

            $this->DeleteThumbnails();

        }
    }

    public function FileTime($thumbnail = null)
    {
        $fp = $this->RealPath($thumbnail);

        return file_exists($fp) ? filemtime($fp) : time();
    }

    public function HtmlView($thumbnail = null, $view = null)
    {

        if ( ! $view ) $view = $this->View;

        $html_output = '';

        $url = $this->URL( $thumbnail, false );

        if ( $view == FieldView::IMAGE ) {

            if ( in_array( $this->FileExt(), array('png', 'jpg', 'gif', 'jpeg', 'svg') ) )

                $html_output = $this->ActiveTemplate()->Widget('file-image-view', array('url' => $url,

                    'title' => ( $this->ActiveDataSet() ? strip_tags($this->ActiveDataSet()->Title) : '' ), 'time' => $this->FileTime($thumbnail) ));

        }

        if ( $view == FieldView::ICON || ( $view == FieldView::IMAGE && is_empty( $html_output ) )  )

            $html_output =  $this->ActiveTemplate()->Widget('file-icon-view', array('url' => $url, 'ext' => $this->FileExt(),

                'title' => ( $this->ActiveDataSet() ? strip_tags($this->ActiveDataSet()->Title) : '' ), 'time' => $this->FileTime($thumbnail)  ));


        if ( $view == FieldView::LINK )

            $html_output = $this->ActiveTemplate()->Widget('file-link-view', array('url' => $url, 'ext' => $this->FileExt(),

                'title' => ( $this->ActiveDataSet() ? strip_tags($this->ActiveDataSet()->Title) : '' ), 'time' => $this->FileTime($thumbnail)  ));


        return $html_output;


    }

    public function Preview()
    {
        return empty( $this->Value ) ? '' : $this->ActiveTemplate()->FilePreview($this);
    }

    public function DefaultHtmlBuilder()
    {
        return strval( $this->HtmlView() );
    }

    public function DefaultTableViewCol()
    {
        $myCol = new TableViewCol($this);

        $myCol->UseHtmlBuilder(function() {

            if ( ! $this->TableViewColThumbnail ) $this->setTableViewColThumbnail();

            return $this->HtmlView($this->TableViewColThumbnail, $this->TableViewColView);

        });

        return $myCol;
    }


    /**
     * @param $value
     * @return File
     */

    public function setFileTypes($value)
    {
        $this->FileTypes = strval($value);

        return $this;
    }

    /**
     * @param $value
     * @return File
     */

    public function setMaxSize($value)
    {
        $this->MaxSize = strval($value);

        return $this;
    }

    /**
     * @param $value
     * @return File
     */

    public function setTargetFileName($value)
    {
        $this->TargetFileName = strval($value);

        return $this;
    }

    /**
     * @param $value
     * @return File
     */

    public function setSubDirectory($value)
    {
        $this->SubDirectory = strval($value);

        return $this;
    }

    /**
     * @param bool $value
     * @return File
     */

    public function setAutoResize($value = true)
    {
        $this->AutoResize = $value;

        return $this;
    }

    /**
     * @param $value
     * @return File
     */

    public function setSourceFile($value)
    {
        $this->SourceFile = $value;

        return $this;
    }


    /**
     * @param array $value
     * @return File
     */

    public function setThumbnails(array $value = array())
    {
        if ( empty( $value ) ) $value = array();

        $this->Thumbnails = $value;

        return $this;
    }

    public function setTableViewColView($value = FieldView::IMAGE)
    {
        $this->TableViewColView = $value;

        return $this;
    }

    public function setTableViewColThumbnail($thumbnail = null)
    {
        if ( ! $thumbnail ) {

            $smallest_width_thumbnail = null;

            $smallest_width = 0;

            foreach ( $this->Thumbnails as $thumb_name => $thumb ) {

                if ( $smallest_width > $thumb['w'] ||  $smallest_width == 0 ) {

                    $smallest_width = $thumb['w'];

                    $smallest_width_thumbnail = $thumb_name;

                }

            }

            $this->TableViewColThumbnail = $smallest_width_thumbnail;

        } else {

            $this->TableViewColThumbnail = $thumbnail;

        }

        return $this;

    }

    /**
     * @param $value
     * @return File
     */
    public function setCleanDataBeforeDeleteFile($value)
    {
        $this->CleanDataBeforeDeleteFile = $value;

        return $this;
    }

}

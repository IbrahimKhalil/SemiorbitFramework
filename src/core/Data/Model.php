<?php
/*
 *-----------------------------------------------------------------------------------------------
* SEMIORBIT - MODEL CLASS                  		 					 semiorbit.com
*------------------------------------------------------------------------------------------------
*/

namespace Semiorbit\Data;




use Semiorbit\Auth\Auth;
use Semiorbit\Component\Services;
use Semiorbit\Debug\FileLog;
use Semiorbit\Field\DataType;
use Semiorbit\Field\Field;
use Semiorbit\Field\File;
use Semiorbit\Field\ID;
use Semiorbit\Field\Password;
use Semiorbit\Field\Validate;
use Semiorbit\Support\Path;
use Semiorbit\Support\Str;
use Semiorbit\Db\DB;
use Semiorbit\Db\Connection;
use Semiorbit\Support\AltaArrayType;
use Semiorbit\Config\Config;
use Semiorbit\Translation\Lang;

/**
 * Model
 *
 * @package Semiorbit
 * @property ID ID
 * @property Field Title
 * @property ID _ID
 */

class Model
{

    const TABLE = null;

    const CONNECTION = null;


    protected $_ID = null;

    protected $_Title = null;

    private $_IDKey = null;

    private $_TitleKey = null;


    private $_TableName;

    private $_Connection;

    private $_DocumentsPath;

    private $_DocumentsRealPath;

    private $_DocumentsURL;

    private $_IsNew = true;

    private $_AutoID;

    private $_EnableAutoID = true;


    private $_Fields = array();

    private $_GroupsProps = array();


    protected $_Package;

    protected $_PackagePrefix;




    use ModelEventsTrait;

    use AccessControlTrait;


    /**
     * This will return table name if it was set using "setTableName()" function.
     * If not, it will use "TABLE" constant, if it was explicitly defined.
     * Otherwise Table name will be set by default as same as DataSet class name, but in snake_case.
     * The snake_case convention adds underscores between words, and convert all letters to lowercase.
     *
     * @return string Table name
     */

    public function TableName()
    {
        if ( ! empty( $this->_TableName ) ) return $this->_TableName;

        $this->_TableName = static::TABLE;

        if ( is_empty( $this->_TableName ) )  $this->_TableName =  Str::SnakeCase( get_class( $this ) );

        return $this->_TableName;

    }

    public function setTableName($table_name)
    {
        if ( ! is_empty( $table_name ) ) $this->_TableName = $table_name;
    }

    /**
     * Set or get connection object
     *
     * @param Connection|string|array $connection
     * @return $this
     */

    public function UseConnection($connection = null)
    {

        if ( $connection instanceof Connection ) $this->_Connection = $connection;

        elseif ( ! empty( $connection ) ) $this->_Connection = DB::Connection( $connection );

        if ( static::CONNECTION && ! isset ( $this->_Connection ) ) $this->_Connection = DB::Connection(static::CONNECTION);

        if ( ! isset ( $this->_Connection ) ) $this->_Connection = DB::ActiveConnection();

        return $this;

    }

    /**
     * @return Connection
     */

    public function ActiveConnection()
    {
        if ( empty( $this->_Connection ) ) $this->UseConnection();

        return $this->_Connection;
    }

    /**
     * Set directory where uploaded documents should be stored, and directory where
     * re-sized images should be stored.
     *
     * @param $documents_path string path to folder where to store uploaded files.
     * @return $this
     */

    public function UseDocuments($documents_path = null)
    {

        if ( is_empty($documents_path) ) $documents_path = Config::DocumentsPath(false);

        $this->_DocumentsPath = Path::Normalize($documents_path);

        if ( ! Path::IsAbsolute($documents_path) ) $documents_path = PUBLICPATH . $documents_path;

        $this->_DocumentsRealPath = $documents_path;

        return $this;

    }

    /**
     * Directory where uploaded documents are stored.
     *
     * @return string
     */

    public function DocumentsPath()
    {
        if ( empty( $this->_DocumentsPath ) ) $this->UseDocuments();

        return $this->_DocumentsPath;
    }

    /**
     * Real path to directory where uploaded documents are stored.
     *
     * @return string
     */

    public function DocumentsRealPath()
    {
        if ( empty( $this->_DocumentsRealPath ) ) $this->UseDocuments();

        return $this->_DocumentsRealPath;
    }


    /**
     * Set directory where uploaded documents should be stored, and directory where
     * re-sized images should be stored.
     *
     * @param $documents_url string path to folder where to store uploaded files.
     * @return $this
     */

    public function UseDocumentsURL($documents_url = null)
    {
        if ( is_empty($documents_url) ) $documents_url = Config::DocumentsURL();

        $this->_DocumentsURL = $documents_url;

        return $this;

    }

    /**
     * Directory where uploaded documents are stored.
     *
     * @return string
     */

    public function DocumentsURL()
    {
        if ( empty( $this->_DocumentsURL ) ) $this->UseDocumentsURL();

        return $this->_DocumentsURL;
    }

    /**
     * Check if data is new.
     *
     * @return bool True or False
     */

    public function IsNew()
    {
        return $this->_IsNew;
    }


    /**
     * Mark data as new, so they will be inserted,
     * if set to false, then data will be updated.
     *
     * @param bool $new
     * @return $this
     */

    public function MarkAsNew($new = true)
    {
        $this->_IsNew = $new;

        return $this;
    }

    /**
     * Generate new ID.
     *
     * @return string|null
     */

    public function AutoID()
    {

        if ( $this->_ID == null ) $this->ID();

        if ( $this->_EnableAutoID && is_empty( $this->_ID->Value ) ) {

            $this->_ID->Value = $this->GenerateUniqueID();

            $this->_AutoID = $this->_ID->Value;

            $this->MarkAsNew( true );

        }

        return $this->_AutoID;

    }

    public function GenerateUniqueID()
    {

        if ( $this->_ID instanceof ID && $this->_ID->DriverUniqueId )

            return $this->ActiveConnection()->Driver()->UniqueId( $this->_ID->DriverUniqueId );

        return uniqid();

    }

    /**
     * Check if auto generating ID is enabled for new model instances
     *
     * @return bool
     */

    public function AutoIDIsEnabled()
    {
        return $this->_EnableAutoID;
    }

    /**
     * Disable auto generating ID for new model instances
     *
     * @param bool|true $disable
     * @return $this
     */

    public function DisableAutoID($disable = true)
    {
        $this->_EnableAutoID = (! $disable);

        return $this;
    }

    /**
     * Check if model ID was generated by AutoID() function
     *
     * @return bool
     */

    public function HasAutoID()
    {
        return $this->_AutoID == $this->_ID->Value;
    }

    /**
     * Read data row from database then fill model fields.
     * If no data was found it will mark model as new and set ID or generates a new one.<p>
     * NB: This will fire onStart event
     *
     * @param string $id Row id to fetch from database.
     * @return static
     */

    public function Read($id = null)
    {

        if ( is_empty($id) || ! $this->ID() ) return $this->NewRecord();


        $id_val = $this->ID->WhereClausePrepareValue($id);


        /** @noinspection SqlNoDataSourceInspection */
        /** @noinspection SqlResolve */
        $sql = "SELECT {$this->ListSelectStmtFields()} FROM {$this->TableName()} WHERE `{$this->ID->Name}` = {$id_val}";


        $myRow = $this->ActiveConnection()->Row( $sql );

        if ( $myRow ) {

            $this->MarkAsNew( false );

            $this->Fill( $myRow, false );


        } else {

            //Row not found

            $this->ResetToDefaults()->MarkAsNew();

            $this->ID->Value = $id;

            $this->onStart();

        }


        return $this;

    }

    /**
     * Reset all fields to default, assign a new auto id
     * and set this model as "NEW"<p>
     * NB: This will fire onStart event
     *
     * @return $this
     */

    public function NewRecord()
    {

        $this->ResetToDefaults()->MarkAsNew()->AutoID();

        $this->onStart();

        return $this;

    }

    /**
     * Check if data source table contains row with specific id
     *
     * @param null $id id to check. If not assigned this model id will be used
     * @return bool
     */

    public function DetectIsNew($id = null)
    {

        $remark = false;

        if ( ! $id ) {

            $id = $this->ID->Value;

            $remark = true;
        }

        $id_val = $this->ID->WhereClausePrepareValue($id);

        /** @noinspection SqlNoDataSourceInspection */
        /** @noinspection SqlResolve */
        $sql = "SELECT {$this->ID->Name} FROM {$this->TableName()} where `{$this->ID->Name}` = {$id_val}";

        $id_exists = $this->ActiveConnection()->Has($sql);

        if ($remark) $this->MarkAsNew( ! $id_exists );

        return ( ! $id_exists );

    }

    /**
     * Fill the model with an array of attributes.<p>
     * NB: This will fire onStart event
     *
     * @param $arr
     * @param bool $check_if_new
     * @param bool $reset_all Reset all fields to its default values
     * @return $this returns this model
     */

    public function Fill($arr, $check_if_new = true, $reset_all = true)
    {

        if ($reset_all) $this->ResetToDefaults();

        if ( ! $arr instanceof Model ) {

            //Fields
            foreach ($this->Fields() as $k => $fld) :

                /**@var Field $fld*/

                if ( isset( $arr[$fld->Name] ) ) {


                    if ( in_array( $fld->Type, array( DataType::VARCHAR, DataType::CHAR, DataType::TEXT, DataType::FILE ) ) ) {

                        $fld->Value = stripslashes( $arr[ $fld->Name ] );

                    } else {

                        $fld->Value = $arr[ $fld->Name ];

                    }

                }

            endforeach;

        } else {

            //Fields
            foreach ($this->Fields() as $k => $fld) :

                    if ( isset( $arr->$k ) ) $fld->Value = $arr->{$k}['value'];

            endforeach;

        }

        if ( $check_if_new ) {

            $sql = "select count(" . $this->ID->Name . ") as chk from " . $this->TableName() . " where " . $this->ID->Name . " = '{$this->ID->Value}' ";

            $chk = $this->ActiveConnection()->Find( $sql );

            $this->MarkAsNew( $chk > 0 ? false : true );
        }


        $this->onStart();

        return $this;

    }

    /**
     * Insert new data or update existing data in database.
     *
     * @return mixed
     */

    public function Save()
    {
        return $this->IsNew() ? $this->InsertRow() : $this->UpdateRow();
    }

    /**
     * Insert model's current data row into database.
     *
     * @return mixed
     */

    public function InsertRow()
    {

        $this->onBeforeInsert();


        if ( is_empty( $this->ID->Value ) )  $this->AutoID();


        if (($validation = $this->Validate()) !== true) return $validation;


        $field_name = [];

        $named_param = [];

        $param_value = [];


        foreach ($this->Fields() as $fld) :

            /** @var Field $fld */


            if ( isset( $fld[Field::AUTO_INCREMENT] ) && $fld[Field::AUTO_INCREMENT] ) continue;

            if ($fld->ReadOnly) continue;



            $field_name[] = "`{$fld->Name}`";

            $named_param[] = $fld->WhereClausePrepareValue(":{$fld->Name}", true);

            $param_value[$fld->Name] = [$fld->Value === '' ? null : $fld->Value, $fld->Type];


        endforeach;

        /** @noinspection SqlNoDataSourceInspection */
        $sql = "INSERT INTO {$this->TableName()} (".join(", ", $field_name).") VALUES (".join(", ",$named_param).")";


        $res = $this->ActiveConnection()->Cmd( $sql, $param_value ) ? Msg::DBOK : Msg::DBERR;


        if ( $res == Msg::DBOK ) {

            if ( $this->ID->offsetExists('AutoIncrement') &&  $this->ID->AutoIncrement ) {

                $inserted_id = $this->ActiveConnection()->LastInsertId();

                $this->RenamwUploadedFiles($this->ID->Value, $inserted_id);

                $this->ID->Value = $inserted_id;

            }

            $this->MarkAsNew( false );

        } else {

            if (Config::DebugMode()) {

                FileLog::Debug("FWK900", "FWK@INSERT", $sql);

                FileLog::Debug("FWK900", "FWK@INSERT-PARAMS", json_encode($param_value, JSON_UNESCAPED_UNICODE));

                $compiled_sql = $sql;

                foreach ($param_value as $k => $v) {


                    $compiled_sql = str_ireplace(":" . $k . ",", $v[0] === null ? "null," : '"'.$v[0].'",', $compiled_sql);

                }

                FileLog::Debug("FWK900", "FWK@INSERT-COMPILED", $compiled_sql);


            }

        }


        $this->onSave($res);

        $this->onInsert($res);

        return $res;

    }


    /**
     * Update model's current data row in database.
     *
     * @return mixed
     */

    public function UpdateRow()
    {

        $this->onBeforeUpdate();


        if (($validation = $this->Validate()) !== true) return $validation;



        $params = [];

        $param_value = [];

        $id_placeholder = "";


        foreach ($this->Fields() as $fld) :

            /**@var $fld Field*/

            if ($fld->ReadOnly) continue;



            $named_placeholder = $fld->WhereClausePrepareValue(":{$fld->Name}", true);


            if ($fld->IsID)

                $id_placeholder = $named_placeholder;


            $params[] = "`{$fld->Name}` = {$named_placeholder}";


            $param_value[$fld->Name] = [$fld->Value === '' ? null : $fld->Value, $fld->Type];


        endforeach;

        /** @noinspection SqlNoDataSourceInspection */
        /** @noinspection SqlResolve */
        $sql="UPDATE {$this->TableName()} SET ".join(", ", $params)." WHERE `{$this->ID['name']}` = {$id_placeholder}";

        //dd($sql);

        //dd($param_value);

        $res = $this->ActiveConnection()->Cmd( $sql, $param_value ) ? Msg::DBOK : Msg::DBERR;

        $this->onSave($res);

        $this->onUpdate($res);

        return $res;
    }

    /**
     * Upload files sent to file fields
     *
     * @return int Upload result (Msg::UPLOAD_OK, Msg::UPLOAD_FAILED, Msg::FILE_TYPE_ERR, Msg::RESIZE_FAILED, Msg::FILE_SIZE_ERR)
     */

    public function UploadFiles()
    {

        foreach ($this->Fields() as $k => $field) {


            if ($field instanceof File) {

                $upload_res = $field->Upload();

                if (  $upload_res != Msg::UPLOAD_OK ) return $upload_res;

            }

        }

        return Msg::UPLOAD_OK;

    }

    public function RenamwUploadedFiles($current_id, $new_id)
    {
        foreach ($this->Fields() as $k => $field)

            if ($field instanceof File) $field->Rename($current_id, $new_id);
    }


    public function Validate()
    {

        if ( ! $this->CheckRequiredValues() )     return Msg::FILL_ALL_REQUIRED;

        if ( ! $this->ValidatePassword() )        return Msg::RETYPE_PASSWORD;

        if ( ! $this->CheckUniqueValues() )       return Msg::DATA_EXISTS;

        //Validate Values-------------------------------------------

        $validate_res = $this->ValidateValues();

        if ( $validate_res !== true  )             return $validate_res;

        //--------------------------------------------------------

        //Upload files-------------------------------------------

        $upload_res = $this->UploadFiles();

        if ( $upload_res != Msg::UPLOAD_OK )        return $upload_res;

        //--------------------------------------------------------

        if ( ($checkpoint = $this->onBeforeSave()) !== null)  return $checkpoint;

        return true;

    }

    public function CheckRequiredValues()
    {

        $res = true;

        foreach ($this->Fields() as $k => $field) :

            /**@var Field $field */

            if ( ! $field->CheckRequiredValue() ) {

                $field->Err[] = "Required";

                if (Config::DebugMode())

                    FileLog::Debug("FWK 300", "FWK@REQUIRED", $field->Name);

                $field->CssClass .= ' val-err';

                $res = false;

            }

        endforeach;

        return $res;

    }


    public function CheckUniqueValues()
    {

        $res = true;

        foreach ($this->Fields() as $k => $field) :

            /**@var Field $field */

            if ($field->Unique) {

                if ( ! $field->CheckUniqueValue() ) {


                    $field->Err[] = Lang::Trans( "semiorbit::msg." . Msg::DATA_EXISTS );

                    $field->CssClass .= ' val-err';

                    $res = false;

                }

            }

        endforeach;

        return $res;

    }

    public function ValidateValues()
    {

        $res = true;

        foreach ($this->Fields() as $k => $field) :

            /**@var Field $field */

            if ( ! $field->ValidateValue() ) {

                if ( $field->Validate == Validate::EMAIL ) $res = Msg::INVALID_EMAIL;

                else if ( $field->Validate == Validate::TEL ) $res = Msg::INVALID_TEL;

                else if ( $field->Validate == Validate::URL ) $res = Msg::INVALID_URL;

                else $res = Msg::INVALID_VALUE;

                $field->Err[] = Lang::Trans( "semiorbit::msg." . $res );

                $field->CssClass .= ' val-err';

            }

        endforeach;

        return $res;

    }

    public function ValidatePassword()
    {
        $res = true;

        foreach ($this->Fields() as $field) :

            if ( $field instanceof Password ) {

                if ( ! $field->ValidatePassword() ) {

                    $field->Err[] = Lang::Trans( "semiorbit::msg." . Msg::RETYPE_PASSWORD );

                    $field->CssClass .= ' val-err';

                    $res = false;

                }

            }

        endforeach;

        return $res;
    }



    /**
     * Remove model's current data row from database.
     *
     * @return bool|int
     */

    public function RemoveRow()
    {

        $checkpoint = $this->onBeforeRemove();

        if ($checkpoint === null || $checkpoint === true) {

            foreach ($this->Fields() as $field) :

                /**@var File $field */

                if (in_array($field->Type, array(DataType::FILE))) {

                    $field->DeleteFiles();
                }

            endforeach;


            $sql = "DELETE FROM {$this->TableName()} WHERE " . $this->ID->Name . " = '" . $this->ID->Value . "'";

            $res = $this->ActiveConnection()->Cmd($sql);

            $this->onRemove($res);

            return $res;

        }

        return $checkpoint;

    }


    /**
     * Get a field by key (Field ID)
     *
     * @param string $key (Field ID) to retrieve
     * @access public
     * @return mixed
     */

    public function __get ($key)
    {

        if ( isset( $this->_Fields[ $key ] ) ) return $this->_Fields[ $key ];

        if (strtoupper($key) == 'ID') return $this->ID();

        if (strtoupper($key) == 'TITLE') return $this->Title();

        return null;

    }


    /**
     * Set a field
     *
     * @param string $key (Field ID) to assign the value to
     * @param mixed  $value to set
     * @access public
     */

    public function __set($key, $value)
    {
        $this->AddField($key, $value);
    }


    /**
     * Whether or not a field exists by key (Field ID)
     *
     * @param string $key (Field ID) to check for
     * @access public
     * @return boolean
     */

    public function __isset ($key)
    {
        return isset( $this->_Fields[ $key ] );
    }


    /**
     * Unset a field by key (Field ID)
     *
     * @param string $key (Field ID) to unset
     * @access public
     */

    public function __unset($key)
    {
        unset( $this->_Fields[ $key ] );
    }


    /**
     * Add new field to model
     *
     * @param string $id Field ID or Database field name
     * @param Field|array|mixed $field
     * @return Field Field
     */

    public function AddField($id, $field)
    {

        if ( $field instanceof Field ) $myField = $field;

        else if ( is_array( $field ) ) $myField = Field::Create( $field );

        else $myField = new Field( array( 'FullName' => $id, 'Value' => $field ) );

        if ( $this instanceof DataSet ) $myField->UseDataSet( $this );

        if ( 'ID' == strtoupper( $id )  ) {

            if ( $this->IDKey() != null && $this->IDKey() != $id ) {

                $this->_ID = $myField;

                $this->_Fields[ $this->_IDKey ] = $myField;

                return $myField;

            }

        }

        if ( 'TITLE' == strtoupper( $id )  ) {

            if ( $this->TitleKey() != null && $this->TitleKey() != $id ) {

                $this->_Title = $myField;

                $this->_Fields[ $this->_TitleKey ] = $myField;

                return $myField;

            }

        }

        $this->_Fields[ $id ] = $myField;

        return $myField;

    }

    /**
     * Bulk add an array of fields
     *
     * @param $flds
     * @return $this
     */

    public function AddFields($flds)
    {
        foreach ( $flds as $name => $fld ) {

            if ( is_numeric( $name ) ) $name = $fld[ 'name' ];

            $this->AddField( $name, $fld );

        }

        return $this;
    }

    /**
     * Model Fields
     *
     * @return array
     */

    public function Fields()
    {
        return $this->_Fields;
    }

    /**
     * Select fields that listed properties match assigned values in selector.
     *
     * @param array $selector List of pairs (prop=>value) to match
     * @param int $array_type
     * @return array Array of fields
     */

    public function SelectFields(array $selector = null, $array_type = AltaArrayType::ASOC )
    {

        if ( $selector == null && $array_type == AltaArrayType::ASOC ) return $this->_Fields;

        $flds = array();
        $n = 0;


        foreach ( $this->_Fields as $k => $fld ) {



            if ( ! is_array( $selector ) ) {

                $this->AddFieldToArray( $flds, $k, $n, $array_type );
                continue;

            } else {

                foreach ( $selector as $prop => $val ) {


                    if (  isset( $fld[ $prop ] ) && ( $val === '?' || $fld[ $prop ] === $val ) ) {

                        $this->AddFieldToArray( $flds, $k, $n, $array_type );
                        continue;

                    }

                }
            }


        }

        return $flds;

    }

    protected function AddFieldToArray(&$arr, $k, &$n, $array_type)
    {

        if ( $array_type == AltaArrayType::BOTH || $array_type == AltaArrayType::ASOC ) $arr[ $k ] = $this->{$k};

        if ( $array_type == AltaArrayType::BOTH || $array_type ==  AltaArrayType::NUM ) $arr[ $n ] = $this->{$k};

        $n++;

    }

    /**
     * Select fields that have listed properties in selector
     *
     * @param array|string $selector property name [or array of properties names] to check availability
     * @param int $array_type
     * @return array
     */

    public function FieldsHave($selector = null, $array_type = AltaArrayType::BOTH ) {

        $has_selectors = array();

        if ( is_array( $selector ) ) {

            foreach ( $selector as $k => $v ) {

                is_int( $k ) ? $has_selectors[ $v ] = '?' : $has_selectors[$k] = $v;

            }

        } else if ( is_string( $selector ) ) {

            $has_selectors[ $selector ] = '?';

        }

        return $this->SelectFields( $has_selectors, $array_type );

    }


    /**
     * Find first field that matches selector
     *
     * @param string|array $selector  Array List of pairs (prop=>value) to match
     *
     * @return Field Returns first field that matches selector or NULL
     */

    public function SelectField(array $selector)
    {
        $flds = $this->SelectFields( $selector, AltaArrayType::NUM );

        return isset( $flds[0] ) ? $flds[0] : null;
    }

    /**
     * Select first field that its field name matches selector.
     *
     * @param string $name Field name selector
     * @return Field Returns first field that matches selector or NULL
     */

    public function FieldByName($name)
    {
        return $this->SelectField( array( 'FullName' => $name ) );
    }

    /**
     * Field
     *
     * @param string $id Field id/key in model
     * @return Field Returns selected field if id exists
     */

    public function Field($id)
    {
        return $this->_Fields[ $id ];
    }

    /**
     * Check if fields array has a field with selected key/id
     *
     * @param string $id Field id/key in model
     * @return bool
     */

    public function HasField($id)
    {
        return isset ( $this->_Fields[ $id ] );
    }

    /**
     * Get model ID field
     *
     * @return Field
     */

    public function ID()
    {

        if ( $this->_ID != null ) return $this->_ID;

        $ids = $this->SelectFields( array( 'IsID' => true ) );


        if ( is_array( $ids ) && count( $ids ) > 0 ) {

            reset($ids);

            $this->_IDKey = key($ids);

            $this->_ID = $ids[ $this->_IDKey ];

            $this->AutoID();

        }

        return $this->_ID;

    }

    /**
     * @return string|null ID field key
     */

    public function IDKey()
    {
        $this->ID();

        return $this->_IDKey;
    }

    /**
     * Get model Title field
     *
     * @return Field
     */

    public function Title()
    {

        if ( $this->_Title != null ) return $this->_Title;

        $titles = $this->SelectFields( array( 'IsTitle' => true ) );

        if ( is_array( $titles ) && count( $titles ) > 0 ) {

            reset($titles);

            $this->_TitleKey = key($titles);

            $this->_Title = $titles[ $this->_TitleKey ];

        }

        return $this->_Title;

    }

    /**
     * @return string|null Title field key
     */

    public function TitleKey()
    {
        $this->Title();

        return $this->_TitleKey;
    }

    /**
     * Mark field as model Title field
     * so it could be accessed using Title() method or property
     *
     * @param Field $fld
     * @return $this
     */

    public function UseFieldAsTitle(Field $fld)
    {

        $old_title = $this->Title();

        $old_title->setIsTitle( false );

        $this->_Title = $fld;

        $fld->setIsTitle( true );

        $this->Title = $this->_Title;

        return $this;

    }

    /**
     * Mark field as model ID field
     * so it could be accessed using ID() method or property
     *
     * @param Field $fld
     * @return $this
     */

    public function UseFieldAsID(Field $fld)
    {

        $old_id = $this->ID();

        if ($old_id != null) $old_id->setIsID( false );

        $this->_ID = $fld;

        $fld->setIsID( true );

        $this->ID = $this->_ID;

        return $this;

    }


    /**
     * Reset model's fields values to its default values.
     *
     * @param bool $clear_all TRUE      Reset all fields values to its default values <br />
     *                        FALSE     Reset only empty fields values to its default values
     *
     * @return $this returns this model
     */

    public function ResetToDefaults($clear_all = true)
    {

        foreach ( $this->Fields() as $fld ) {

            /**@var Field $fld */

            if ( $clear_all === false &&  $fld->Value !== null ) continue;

            $fld->ResetToDefault();

        }

        return $this;

    }

    /**
     * Array of model fields groups settings & properties
     *
     * @return array
     */

    public function GroupsProps()
    {
        return $this->_GroupsProps;
    }

    public function setGroupsProps($props)
    {
        $this->_GroupsProps = $props;

        return $this;
    }

    /**
     * Array of model fields groups
     *
     * @param bool $include_hidden
     * @return array
     */

    public function Groups($include_hidden = false)
    {
        $groups = array();

        foreach ($this->Fields() as $k => $fld) {

            if (! $include_hidden)
            {
                if (in_array($fld['control'], array('none'))) continue;

                if ($fld['permission']!==null && ! Auth::Check($fld['permission'])) continue;
            }

            if ( isset( $fld['group'] )) {

                $group = $fld['group'];

                // Adding group properties, only if not added previously

                if ( isset( $this->_GroupsProps[ $group ] ) ) {

                    foreach ($this->_GroupsProps[$group] as $prop => $prop_value) {

                        $groups[$group][$prop] = $prop_value;

                    }

                }

                // Setting default group_id

                if ( ! isset( $groups[ $group ] ['id'] ) ) {

                    $groups[ $group ] ['id'] =  $group;

                }

                // Setting default caption as group_id

                if ( ! isset( $groups[ $group ] ['caption'] ) ) {

                    $groups[ $group ] ['caption'] = defined($group) ? constant($group) : Lang::Trans( $this->PackagePrefix() . Str::ParamCase( static::ModelName() ) . '.' . $group );

                }

                // Add field

                $groups[ $group ] ['items'] [$k] = $fld;

            } else {

                $groups[0] ['items'] [$k] =  $fld;

            }

        }

        return $groups;
    }

    /**
     * Prepare model's fields list for sql select statement.<br/>
     * If separator is set to FALSE||NULL an array will be returned.
     * Otherwise comma separated list of fields select-expr or names wil be returned.
     *
     * @param bool $declare_table_name
     * @param bool $return_array
     * @param string $separator
     * @return array|string
     */

    public function ListSelectStmtFields($declare_table_name = true, $return_array = false, $separator = ', ')
    {

        $list = array();

        foreach ( $this->Fields() as $fld ) {

            /**@var Field $fld */

            if ( $fld->ExcludeFromSelect ) continue;

            $list[] = $fld->SelectExpr($declare_table_name);

        }

        return $return_array ? $list : implode($separator, $list);

    }

    /**
     * Row array of model
     * (Field->FullName => Field=>Value) pairs
     *
     * @return array
     */

    public function ToArrayRow()
    {
        $arr = array();

        foreach ($this->Fields() as $k => $fld) {

            $arr[ $fld->Name ] = $fld->Value;

        }

        return $arr;
    }

    /**
     * Model to array of (Field Key => Field Props Array) list
     *
     * @param array $props Include more props other than name and value
     * @return array
     */

    public function ToArray(array $props = array())
    {

        $arr = array();

        foreach ($this->Fields() as $k => $fld) {

            $arr[ $k ] = array( 'FullName' => $fld->Name, 'Value' => $fld->Value );

            foreach( $props as $prop ) $arr[ $k ] [ $prop ] = $fld[ $prop ];
        }

        return $arr;

    }


    public function __toString()
    {
        return $this->Title->Value ?? '';
    }

    public static function ModelName()
    {
        return Path::ClassShortName(static::class);
    }

    public function Package()
    {
        // TODO: Package from cache

        return $this->_Package ?: $this->_Package = Services::FindPackageByModelNs($this->Namespace());
    }

    public function PackagePrefix()
    {
        return $this->_PackagePrefix ?: $this->_PackagePrefix = (($pkg = $this->Package()) ? $pkg . '::' : '');
    }


}
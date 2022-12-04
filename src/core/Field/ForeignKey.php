<?php
namespace Semiorbit\Field;

use Semiorbit\Data\DataSet;
use Semiorbit\Db\Connection;
use Semiorbit\Db\DB;

/**
 * Trait ForeignKey
 * @method DataSet ActiveDataSet()
 * @property DataSet $FKeyDataSet
 * @property Field $FKeyValueField
 * @property Field $FKeyTextField
 * @package Semiorbit\Field
 */
trait ForeignKey
{

    public $FKeyValueFieldName;

    public $FKeyTextFieldName;

    public $FKeyTable;

    public $FKeyOrderBy;

    public $FKeyFilter;

    public $FKeyDataSet;

    public $FKeyValueField;

    public $FKeyTextField;


    protected $_FKeyLoadMethod = Select::FKEY_PRELOAD_ALL;

    protected $_FKeyResultCol;

    protected $_Cache_FKeyOptions;

    protected $_Cache_FKeyLoadedItems = array();

    protected $_Cache_External = false;

    protected $_LastSelectedValues;

    protected $_Connection;


    public function setForeignKey($table, $value_field, $text_field)
    {

        $this->FKeyDataSet = null;

        $this->setFKeyTable($table);

        $this->setFKeyValueFieldName($value_field);

        $this->setFKeyTextFieldName($text_field);


        if (!$this->_TypeDefined) {

            $this->Type = DataType::INT;

            $this->MaxLength = 20;

        }


        return $this;

    }

    public function setForeignKeyBy($data_set, $value_field_id = null, $text_field_id = null)
    {


        $data_set = $data_set instanceof DataSet ? $data_set : new $data_set;

        $this->setFKeyTable($data_set->TableName());

        $value_field = $value_field_id ? $data_set->Field($value_field_id) : $data_set->ID();

        $text_field = $text_field_id ? $data_set->Field($text_field_id) : $data_set->Title();

        $this->setFKeyValueFieldName($value_field->Name);

        $this->setFKeyTextFieldName($text_field->Name);


        $this->Type = $value_field->Type;

        $this->MaxLength = $value_field->MaxLength;

        $this->Unsigned = $value_field->Unsigned;

        return $this;

    }

    public function setRelationTo($data_set, $value_field_id = null, $text_field_id = null)
    {

        $this->FKeyDataSet = $data_set instanceof DataSet ? $data_set : new $data_set;

        $this->setFKeyTable($this->FKeyDataSet->TableName());

        $this->FKeyValueField = $value_field_id ? $this->FKeyDataSet->Field($value_field_id) : $this->FKeyDataSet->ID();

        $this->FKeyTextField = $text_field_id ? $this->FKeyDataSet->Field($text_field_id) : $this->FKeyDataSet->Title();

        $this->setFKeyValueFieldName($this->FKeyValueField->Name);

        $this->setFKeyTextFieldName($this->FKeyTextField->Name);


        $this->Type = $this->FKeyValueField->Type;

        $this->MaxLength = $this->FKeyValueField->MaxLength;

        $this->Unsigned = $this->FKeyValueField->Unsigned;

        return $this;

    }


    public function ForeignKeyLoadMethod()
    {
        return $this->_FKeyLoadMethod;
    }


    /**
     * NB. FKEY_EAGER_LOADING method doesn't work with table->Read()
     * use table->Row() instead because all rows in reault should be loaded to index keyword
     *
     * @return $this
     */
    public function setForeignKeyLoadMethod($method = Select::FKEY_PRELOAD_ALL, $result_col = null)
    {
        $this->_FKeyLoadMethod = $method;

        $this->_FKeyResultCol = $result_col;

        return $this;
    }

    public function ForeignKeyOptions()
    {

        if ($this->_Cache_FKeyOptions) return $this->_Cache_FKeyOptions;

        if ( ! ( $this->FKeyTable && $this->FKeyTextFieldName && $this->FKeyValueFieldName ) ) return array();

        $order_by = ($this->FKeyOrderBy === null) ? (($this->FKeyOrderBy === '') ? ""

            : " ORDER BY {$this->FKeyTextFieldName} ")

            : " ORDER BY {$this->FKeyOrderBy} ";

        $fkey_options = array();

        if ($this->FKeyDataSet) {

            //TODO: List important fields only

            $tbl = $this->ActiveConnection()

                ->Table("SELECT {$this->FKeyDataSet->ListSelectStmtFields()}  FROM {$this->FKeyTable} {$this->FKeyFilter()}  {$order_by}");

            while ($row = $tbl->Row()) {

                $this->FKeyDataSet->Fill($row);

                $fkey_options[$this->FKeyValueField->Value] = $this->FKeyTextField->Html();

                $this->_Cache_FKeyLoadedItems[$this->FKeyValueField->Value] = $row;

            }

        } else {

            $tbl = $this->ActiveConnection()

                ->Table("SELECT {$this->FKeyValueFieldName}, {$this->FKeyTextFieldName}  FROM {$this->FKeyTable} {$this->FKeyFilter()}  {$order_by}");

            foreach ($tbl->Rows() as $row) {

                $fkey_options[$row[$this->FKeyValueFieldName]] = $row[$this->FKeyTextFieldName];

                if ($this->_Cache_External)

                    $this->_Cache_FKeyLoadedItems[$row[$this->FKeyValueFieldName]] = $row[$this->FKeyTextFieldName];

            }

        }

        unset($tbl);

        return $this->_Cache_FKeyOptions = $fkey_options;

    }

    public function FKeyLoadItem($key, $return_row = false)
    {

        if ($this->FKeyDataSet) {

            $this->_Cache_FKeyLoadedItems[$key] = $this->ActiveConnection()

                ->Row("SELECT {$this->FKeyDataSet->ListSelectStmtFields()} FROM {$this->FKeyTable} WHERE {$this->FKeyValueFieldName} = " . $this->FKeyValueField->WhereClausePrepareValue($key));

            $this->FKeyDataSet->Fill($this->_Cache_FKeyLoadedItems[$key]);

            return $return_row ? $this->_Cache_FKeyLoadedItems[$key] : $this->FKeyTextField->Html();

        } else {

            return $this->_Cache_FKeyLoadedItems[$key] = htmlspecialchars($this->ActiveConnection()

                ->Find("SELECT {$this->FKeyTextFieldName} FROM {$this->FKeyTable} WHERE {$this->FKeyValueFieldName} = '{$key}' "));

        }

    }
    
    public function FKeyLoadItemFromCache($key, $return_row = false) 
    {
        
        if (isset( $this->_Cache_FKeyLoadedItems[$key] )) {

            if ($this->FKeyDataSet) {

                $this->FKeyDataSet->Fill($this->_Cache_FKeyLoadedItems[$key]);

                return $return_row ? $this->_Cache_FKeyLoadedItems[$key] : $this->FKeyTextField->Html();

            } else {

                return $this->_Cache_FKeyLoadedItems[$key];

            }

        }
        
        return null;
        
    }

    /**
     * NB. This method doesn't work with table->Read()
     * use table->Row() instead because all rows in result should be loaded to index keyword
     *
     * @return $this
     */
    public function FKeyExecuteEagerLoad()
    {

        $loaded_rows = $this->ActiveDataSet()->Table()->Rows();

        $values = array();

        if ($this->FKeyDataSet) {

            foreach ($loaded_rows as $row)

                $values[$row[$this->Name]] = $this->FKeyValueField->WhereClausePrepareValue("{$row[$this->Name]}");

        } else {

            foreach ($loaded_rows as $row)

                $values[$row[$this->Name]] = "'{$row[$this->Name]}'";

        }

        $keys = implode(", ", $values);


        $fields_list = $this->FKeyDataSet ? "{$this->FKeyDataSet->ListSelectStmtFields()}" : "{$this->FKeyValueFieldName}, {$this->FKeyTextFieldName}";

        $items_list = $this->ActiveConnection()

               ->Table("SELECT {$fields_list} FROM {$this->FKeyTable} WHERE {$this->FKeyValueFieldName} IN ({$keys})");


        while ($row = $items_list->Row()) {

            $this->_Cache_FKeyLoadedItems[$row[$this->FKeyValueFieldName]] = $this->FKeyDataSet ? $row : $row[$this->FKeyTextFieldName];

        }

        return $this;

    }

    public function FKeyText($key)
    {
        return $this->FKeyItem($key, false);
    }

    public function FKeyItem($key, $return_row = true)
    {

        switch ($this->ForeignKeyLoadMethod()) {

            case Select::FKEY_PRELOAD_ALL:
            default:

                if ($return_row) {

                    $this->ForeignKeyOptions();

                    return $this->FKeyLoadItemFromCache($key, $return_row) ?:  $this->FKeyLoadItem($key, $return_row);

                }

                //If using external cache dataset should be re-filled

                return $this->_Cache_External ? ($this->FKeyLoadItemFromCache($key)

                        ?: ( isset($this->ForeignKeyOptions()[$key]) ? $this->ForeignKeyOptions()[$key] : null ))

                    : ( isset($this->ForeignKeyOptions()[$key]) ? $this->ForeignKeyOptions()[$key] : null );

                break;

            case Select::FKEY_LAZY_LOADING:

                return $this->FKeyLoadItem($key, $return_row);
                break;

            case Select::FKEY_LOAD_MISSING:

                return $this->FKeyLoadItemFromCache($key, $return_row) ?:  $this->FKeyLoadItem($key, $return_row);
                break;

            case Select::FKEY_EAGER_LOADING:

                if (! isset($this->_Cache_FKeyLoadedItems[$key])) $this->FKeyExecuteEagerLoad();

                return $this->FKeyLoadItemFromCache($key, $return_row);
                break;

            case Select::FKEY_FROM_RESULT:

                if ($return_row)

                    $this->FKeyLoadItemFromCache($key, $return_row) ?:  $this->FKeyLoadItem($key, $return_row);

                return $this->ActiveDataSet()->Table()->CurrentRowItem($this->_FKeyResultCol, false);
                break;

        }

    }

    public function RelatedByItemKey($key)
    {

        // Fill data from Cache to related data set, if it is not already filled.

        if ( $this->FKeyValueField->Value != $key )

            $this->FKeyDataSet->Fill( $this->FKeyItem($key) );

        return $this->FKeyDataSet;

    }

    /**
     * Related dataset
     *
     * @return DataSet
     */

    public function Related()
    {
        /**@var $this Select */

        return $this->RelatedByItemKey($this->Value);
    }

    /**
     * Related dataset with multiple related rows
     *
     * @param int $selected_option_number
     * @param bool $refresh_selected_table To reload data rows into related data set table.
     * @return DataSet
     */

    public function RelatedItems($selected_option_number = 0, $refresh_selected_table = false)
    {

        // Fill data from Cache to related data set, if it is not already filled.

        /**@var $this Select */

        $selected_values = $this->SelectedValues();

        if ( $refresh_selected_table || $this->_LastSelectedValues != $selected_values ) {

            $this->FKeyDataSet->Table()->ClearRows();

            foreach ($selected_values as $value) {

                // Trigger Loading

                $row = $this->FKeyItem($value);

                $this->FKeyDataSet->Table()->AddRow($row, $value);

            }

            $this->_LastSelectedValues = $selected_values;

        }

        $selected_value = $selected_values[$selected_option_number];

        if ( $refresh_selected_table || $this->FKeyValueField->Value != $selected_value )

            $this->FKeyDataSet->Fill( $this->FKeyDataSet->Table()->RowByKey($selected_value) );


        return $this->FKeyDataSet;

    }

    public function UseCacheArray(&$cache_array)
    {

        if ($cache_array === null) {

            $this->_Cache_FKeyLoadedItems = array();

            $this->_Cache_External = false;
        }

        $this->_Cache_External = true;

        $this->_Cache_FKeyLoadedItems =& $cache_array;

        return $this;

    }

    public function ActiveCacheArray()
    {
        return $this->_Cache_FKeyLoadedItems;
    }

    /**
     * Set connection object
     *
     * @param Connection|string|array $connection
     * @return static
     */

    public function UseConnection($connection = null)
    {

        if ( $connection instanceof Connection ) $this->_Connection = $connection;

        elseif ( ! empty( $connection ) ) $this->_Connection = DB::Connection( $connection );

        elseif ( ( $this->ActiveDataSet() instanceof DataSet ) ) $this->_Connection = $this->ActiveDataSet()->ActiveConnection();

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

    public function FKeyFilter()
    {

        $filter = trim($this->FKeyFilter);

        if (empty($filter)) return '';

        return starts_with(strtolower($filter), "where") ? $this->FKeyFilter : "WHERE {$this->FKeyFilter}";

    }

    public function setFKeyValueFieldName($value)	 { $this->FKeyValueFieldName	=  strval( $value ); return $this; }

    public function setFKeyTextFieldName($value)	 { $this->FKeyTextFieldName	    =  strval( $value ); return $this; }

    public function setFKeyTable($value)		     { $this->FKeyTable	        =  strval( $value ); return $this; }

    public function setFKeyOrderBy($value)		     { $this->FKeyOrderBy	    =  strval( $value ); return $this; }

    public function setFKeyFilter($value)		     { $this->FKeyFilter	    =  strval( $value ); return $this; }





}
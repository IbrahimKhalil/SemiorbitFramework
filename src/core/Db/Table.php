<?php
/*
*-----------------------------------------------------------------------------------------------
* DB TABLE ARRAY OBJECT					  							    	    semiorbit.com
*-----------------------------------------------------------------------------------------------
*
*/

namespace Semiorbit\Db;

use Semiorbit\Data\DataSet;
use Semiorbit\Data\Model;
use Semiorbit\Output\Pagination;




/**
 * Class Table
 * @package Semiorbit\Db
 *
 * @property DataSet _DataSet
 *
 */

class Table extends QueryBuilder implements \ArrayAccess, \IteratorAggregate, \Countable
{

    protected $_Connection;

    protected $_Rows = [];

    protected $_Iterator = '\ArrayIterator';

    protected $_Result;

    protected $_RefreshRows = true;

    protected $_PK;

    protected $_Indices = [];

    protected $_Fields = [];

    protected $_DataSet;

    protected $_Instantiate = false;

    protected $_CurrentKey;

    protected $_KeysIndex;

    protected $_TotalQuery;

    protected $_Pagination;

    protected $_Total;

    protected $_PageCount;

    /**
     * @param mixed $selector   string > Sql select statement<br/>
     *                          Object > SqlQuery<br/>
     *                          string > Table name<br/>
     *                          Object > Table<br/>
     */

    public function __construct($selector)
    {
        $this->From($selector);
    }

    /**
     * @param mixed $selector   string > Sql select statement<br/>
     *                          Object > SqlQuery<br/>
     *                          string > Table name<br/>
     *                          Object > Table<br/>
     * @return Table
     */

    public function From($selector)
    {

        if ( is_string( $selector ) ) {

            parent::From( $selector );

        } elseif ( is_array( $selector ) ) {

            $this->CFlag(false)->DFlag(true);

            $this->_RefreshRows = false;

            $this->_Rows = $selector;

        } elseif ( $selector instanceof Table ) {

            $this->_Rows = $selector->Rows();

        }

        return $this;

    }

    /**
     * Set connection object
     *
     * @param Connection|string|array $connection
     * @return Table
     */

    public function UseConnection($connection = null)
    {

        if ( $connection instanceof Connection ) $this->_Connection = $connection;

        elseif ( ! empty( $connection ) ) $this->_Connection = DB::Connection( $connection );

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
     * @param DataSet $data_set
     * @return $this
     */

    public function UseDataSet(DataSet $data_set)
    {
        $this->_DataSet = $data_set;

        $this->_Iterator = $this->_DataSet ? '\Semiorbit\Data\ModelsIterator' : '\ArrayIterator';

        return $this;
    }

    /**
     * @return DataSet|null
     */

    public function ActiveDataSet()
    {
        return ( $this->_Instantiate && $this->_DataSet ) ? $this->_DataSet->Create() : $this->_DataSet;
    }

    /**
     * Create a new instance of related DataSet whenever it is used - Otherwise<p>
     * by default the same instance of DataSet object will be filled with row data -<p>
     * on every iteration or row retrieval
     *
     * @param bool|true $instantiate
     * @return $this
     */

    public function InstantiateDataSet($instantiate = true)
    {
        $this->_Instantiate = $instantiate;

        return $this;
    }

    /**
     * @return bool
     */

    public function InstantiateDataSetEnabled()
    {
        return $this->_Instantiate;
    }


    /**
     * @return DataSet|array|bool returns data row as an associative array or active DataSet
     */

    public function Read()
    {

        $row = $this->ActiveConnection()->Driver()->Fetch( $this->Result() );

        if ( $row === null ) {

            $this->_RefreshRows = false;

            return false;

        }

        $this->_Rows[] = $row;

        end( $this->_Rows );

        $this->_CurrentKey = key( $this->_Rows );

        if ( empty( $this->_Fields ) ) $this->_Fields = array_keys( $this->_Rows[ $this->_CurrentKey ] );

        $this->BuildIndicesForRow( $this->_CurrentKey );

        return ( $this->ActiveDataSet() && $row ) ?

            $this->ActiveDataSet()->Fill( $row, false )->MarkAsNew(false) : $row;


    }

    public function Load()
    {

        if ( $this->StateChanged() ) {

            $this->ClearRows();

            $this->_Rows = $this->ActiveConnection()->Driver()->FetchAll( $this->Result() ) ?: [];

            $this->_RefreshRows = false;

            if ( isset( $this->_Rows[ 0 ] ) ) $this->_Fields = array_keys( $this->_Rows[ 0 ] );

            $this->BuildIndices();



        }

        return $this;

    }

    public function Rows($load = true)
    {
        if ($load) $this->Load();

        return $this->_Rows;
    }

    /**
     * Read row from LOADED rows in <u>Table</u> rows array (will not reload from database except if result not loaded yet)<p>
     * by ID field or row index <p><p>
     * If ID is <b>NULL</b>, it will read <b>next row</b> in Table rows array<p><p>
     *
     * NB: This will fire <b>onStart</b> event in active DataSet on load complete
     *
     *
     * @param $id mixed row id or row index in table
     * @return DataSet|array|False returns row array, or fills active DataSet with data from selected row . This will return FALSE if no row was found
     */

    public function Row($id = null)
    {

        $this->Load();

        if ( $id === null ) {

            $row = current( $this->_Rows );

            $this->_CurrentKey = key( $this->_Rows );

            next( $this->_Rows );

        } else {

            $row = $this->RowByID($id);

        }

        return ( $this->ActiveDataSet() && $row && !$row instanceof Model ) ?

            $this->ActiveDataSet()->Fill( $row, false )->MarkAsNew(false) : $row;

    }

    public function RowByID($id)
    {

        $row_id = $this->FindRowID( $id );

        if ( ! $row_id ) return false;

        $row = $this->_Rows[$row_id];

        return $row;

    }

    public function Item($row_id, $col_id)
    {
        $row = $this->RowByID($row_id);

        return isset($row[$col_id]) ? $row[$col_id] : null;
    }

    public function AddRow($row, $key = null)
    {
        $this->offsetSet($key, $row);
    }

    public function Execute()
    {

        $this->_Result = $this->ActiveConnection()->Cmd( $this->QueryString(), $this->Params() );

        $this->_RefreshRows = true;

        return $this;

    }

    public function Columns()
    {

        if ($this->ActiveDataSet()) $this->Select( $this->ActiveDataSet()->ListSelectStmtFields(true, true) );

        return parent::Columns();

    }

    public function Result($execute = true)
    {
        if ( $execute && ( $this->_QueryChangedFlag || $this->_Result === null ) ) $this->Execute();

        return $this->_Result;
    }

    /**
     * Number of loaded rows from database to result set
     *
     * @return int
     */

    public function RowCount()
    {
        return $this->ActiveConnection()->RowCount( $this->Result() );
    }

    public function StateChanged()
    {
        return $this->_RefreshRows || $this->_QueryChangedFlag;
    }

    public function PK($force_check = false)
    {

        if ( empty( $this->_PK ) || $force_check ) {

            $this->_PK = $this->ActiveDataSet() ? $this->ActiveDataSet()->ID->Name : null;


            if ( $this->_PK === null )

                $this->_PK = $this->ActiveConnection()

                    ->Find("SHOW KEYS FROM {$this->TableName()} WHERE Key_name = 'PRIMARY' ", [], "Column_name");

        }


        return $this->_PK;

    }

    public function TableName()
    {

        if ( empty ( $this->_Table ) ) {

            $tbl_name = $this->ActiveConnection()

                            ->Find( "EXPLAIN " . $this->QueryString(), $this->Params(), "table" );

            $this->_Table = $tbl_name;

            return $tbl_name;

        } else {

            return parent::TableName();

        }

    }

    public function setTotalQuery($query)
    {
        $this->_TotalQuery = $query;

        return $this;
    }

    protected function BuildTotalQuery()
    {

        if ( ( ! $this->_QueryChangedFlag ) && $this->_TotalQuery ) return $this->_TotalQuery;

        if ( $this->_DirectSqlFlag ) {

            //TODO: Build proper count query for UNION and DISTINCT cases
            //TODO: Replace COUNT with SQL_CALC_FOUND_ROWS for MySQL only avoiding just browsing case >> REVISE: phpMyAdmin code

            //Check for UNION
            if ( stripos( $this->_QueryString, 'UNION' ) ) return false;

            //Get SELECT statement
            $first_from_pos = stripos( $this->_QueryString, 'FROM' );

            if ( ! $first_from_pos ) return false;

            $select_str = substr( $this->_QueryString, 0, $first_from_pos );

            if ( stripos( $select_str, 'DISTINCT' ) ) return false;

            $this->_TotalQuery = 'SELECT COUNT(*) ' .

                    static::ClipLimit( substr( $this->_QueryString, $first_from_pos ) );


        } else {

            $pk = $this->PK();

            $query_parts['select_string'] = $this->_DirectSqlFlag ? $this->SelectString() :

                "SELECT COUNT(" . ($pk ? $pk : "*") . ") FROM {$this->TableName()}";

            $query_parts['where_string'] = $this->WhereString();

            $this->_TotalQuery = implode(' ', $query_parts);

        }

        return $this->_TotalQuery;

    }

    public function Total()
    {

        if ( ( ! $this->_QueryChangedFlag ) && $this->_Total !== null ) return $this->_Total;

        $count_query = $this->BuildTotalQuery();


        if ( ! $count_query  ) {

            $totalResult = $this->ActiveConnection()->Table( static::ClipLimit( $this->_QueryString ) );

            $total = $totalResult->RowCount();

        } else {

            $total = $this->ActiveConnection()->Find( $count_query );

        }

        if ( ! $total ) $total = 0;

        return $this->_Total = $total;

    }

    public function PageCount()
    {

        if ( ( ! $this->_QueryChangedFlag ) && $this->_PageCount ) return $this->_PageCount;

        return $this->_PageCount = ($this->_Limit ? ceil( $this->Total() / $this->_Limit ) : 1);

    }

    public function Paginate($rows_per_page = null)
    {
        $this->Limit($rows_per_page)->Page( $this->Pagination()->RequestedPage() );

        return $this;
    }

    /**
     * @return Pagination
     */

    public function Pagination()
    {
        if ( ! $this->_Pagination ) $this->_Pagination = new Pagination($this);

        return $this->_Pagination;
    }

    public function Fields()
    {
        return $this->_Fields;
    }

    public function HasField($field_name)
    {
        return in_array( $field_name, $this->Fields() );
    }

    public function IndexByPK($pk = null)
    {

        $this->_PK = empty( $pk ) ? $this->PK() : $pk;

        $this->Load();

        if ( ! $this->HasField( $this->_PK ) ) return $this;


        //TODO: SHORTEN THESE LOOPS WHEN ID IS UNIQUE========

        $temp_rows = array();

        $cur_row = current( $this->_Rows );

        foreach ( $this->_Rows as $k => $row ) {

            $temp_rows[ $row[ $this->_PK ] ] = $row;

            unset( $this->_Rows[ $k ] );

        }


        foreach ( $temp_rows as $k => $row ) {

            $this->_Rows[$k] = $row;

            unset( $temp_rows[$k] );

        }

        //Reset rows array internal pointer to its position before indexing started

        reset($this->_Rows);

        do {

            if ( $cur_row === current( $this->_Rows ) ) break;

        } while( next( $this->_Rows ) );


        unset($temp_rows);

        $this->_Indices[ 'pk' ] = $this->_PK;

        return $this;

    }

    public function ClearIndex($index)
    {

        if (  ( $index == 'pk' || $index == $this->_PK ) && isset ( $this->_Indices['pk'] ) ) {

            unset ( $this->_Indices['pk'] );

        }


        $index_key = array_search( $index, $this->_Indices );

        unset ( $this->_Indices [ $index_key ] );

        return $this;

    }

    public function BuildIndices()
    {
        foreach( $this->_Indices as $index_key => $index ) {

            if ( $index_key == $this->_PK || $index_key == 'pk' ) {

                $this->IndexByPK();

            }else{

                //TODO Index Builder

            }

        }
    }

    protected function BuildIndicesForRow($key)
    {

        foreach( $this->_Indices as $index_key => $index ) {

            if ( $index_key == $this->_PK || $index_key == 'pk' ) {

                $row = $this->_Rows[ $key ];

                if ( isset( $row[ $this->_PK ] ) )

                    $this->_Rows[ $row[ $this->_PK ] ] = $row;

                unset ( $this->_Rows[ $key ] );

            }else{

                //TODO Index Builder

            }

        }

    }


    public function RefreshRows()
    {

        $this->_RefreshRows = true;

        $this->Load();

        return $this;

    }

    public function ClearRows()
    {
        $this->_Rows = [];

        $this->_Fields = [];

        return $this;
    }

    public function Refresh()
    {
        $this->ClearRows();

        $this->Execute();

        $this->Load();

        return $this;
    }

    /**
     * Whether or not an data exists by key
     *
     * @param string $key An data key to check for
     * @access public
     * @return boolean
     * @abstracting ArrayAccess
     */

    public function __isset ($key)
    {
        $this->Load();

        return isset ( $this->_Rows[ $key ] );
    }


    /**
     * Unset an data by key
     *
     * @param string $key The key to unset
     * @access public
     */

    public function __unset($key)
    {
        $this->Load();

        unset( $this->_Rows[ $key ] );
    }


    /**
     * Assigns a value to the specified offset
     *
     * @param string $key The offset to assign the value to
     * @param mixed  $value The value to set
     * @access public
     * @abstracting ArrayAccess
     */

    public function offsetSet($key, $value)
    {

        $this->Load();

        if ( ! is_array( $value ) || $value instanceof Model ) $value = null;

        if ( is_null( $key ) ) {

            $row_id = null;

            if ( is_array( $value ) && $this->PK() ) $row_id = $value[ $this->PK() ];

            else if ( $value instanceof Model ) $row_id = $value->ID->Value;

            $row_id ? $this->_Rows[ $row_id ] = $value : $this->_Rows[] = $value;

        } else {

            $row_id = $this->FindRowID($key) ?: $key;
            
            $this->_Rows[ $row_id ] = $value;

        }

        $this->_KeysIndex = false;

    }


    /**
     * Whether or not an offset exists
     *
     * @param string $key An offset to check for
     * @access public
     * @return boolean
     * @abstracting ArrayAccess
     */

    public function offsetExists($key)
    {
        $this->Load();

        return isset( $this->_Rows[ $key ] );
    }

    /**
     * Unsets an offset
     *
     * @param string $key The offset to unset
     * @access public
     * @abstracting ArrayAccess
     */

    public function offsetUnset($key)
    {

        $this->Load();

        $row_id = $this->FindRowID($key);

        if ( $row_id ) {

            unset( $this->_Rows[ $key ] );

            $this->_KeysIndex = false;

        }

    }

    /**
     * Returns the value at specified offset
     *
     * @param string $key The offset to retrieve
     * @access public
     * @return mixed
     * @abstracting ArrayAccess
     */

    public function offsetGet($key)
    {
        $this->Load();

        $row_id = $this->FindRowID($key);

        $row = $row_id ? $this->_Rows[ $row_id ] : null;

        return ( $this->ActiveDataSet() && $row && !$row instanceof Model ) ?

             $this->ActiveDataSet()->Fill( $row, false )->MarkAsNew(false) : $row;

    }

    /**
     * An instance of the object implementing Iterator or Traversable
     *
     * @access public
     * @return object
     * @abstracting IteratorAggregate
     */

    public function getIterator()
    {
        $this->Load();

        return $this->_Iterator == '\ArrayIterator' ? new $this->_Iterator( $this->_Rows ) :

            new $this->_Iterator( $this->_Rows, $this );

    }

    /**
     * Count all rows
     *
     * @access public
     * @param bool $load TRUE to load data first, if query has been changed or not executed yet.
     * @return int
     * @abstracting Countable
     */

    public function Count($load = true)
    {

        if ($load) $this->Load();

        $count = count( $this->_Rows );

        return $count;

    }


    /**
     * Returns Array
     *
     * @access public
     * @return array
     */

    public function ToArray()
    {
        $this->Load();

        return $this->_Rows;
    }

    /**
     * Merge multiple arrays with data
     *
     * @param array $array1 List of arrays or Table(s) to merge. One array at least
     * @param array $_ Optional more arrays to merge.
     * @access pubic
     * @return array
     */

    public function Merge($array1, $_ = null)
    {

        $this->Load();

        $arg_list = func_get_args();

        foreach ($arg_list as $k => $arr)
        {

            if ( ! is_array( $arr ) )
                if ( is_object( $arr ) ){

                    if ( method_exists( $arr, 'toArray' ) ) {

                        $arg_list[ $k ] = $arr->ToArray();

                    }

                } else {

                    unset( $arg_list[ $k ] );

                }



        }

        array_unshift( $arg_list, $this->_Rows );

        $res_arr = call_user_func_array( "array_merge", $arg_list );

        $this->_Rows = $res_arr;

        return $this->_Rows;

    }

    public function __toString()
    {
        $this->Load();

        return json_encode( $this->_Rows );
    }


    /**
     * Current row key in rows array
     *
     * @param bool $load
     * @return mixed
     */

    public function Key($load = false)
    {
        if ($load) $this->Load();

        return $this->_CurrentKey;
    }


    public function CurrentRow($load = false)
    {
        if ($load) $this->Load();

        return isset($this->_Rows[$this->_CurrentKey]) ? $this->_Rows[$this->_CurrentKey] : null;
    }

    public function ItemByRowKey($key, $col, $load = false)
    {
        if ($load) $this->Load();

        return isset($this->_Rows[$key][$col]) ? $this->_Rows[$key][$col] : null;
    }


    public function RowByKey($key, $load = false)
    {
        if ($load) $this->Load();

        return isset($this->_Rows[$key]) ? $this->_Rows[$key] : null;
    }

    public function CurrentRowItem($col, $load = false)
    {
        if ($load) $this->Load();

        return isset($this->_Rows[$this->_CurrentKey][$col]) ? $this->_Rows[$this->_CurrentKey][$col] : null;
    }

    /**
     * Move rows array pointer to first row
     *
     * @return $this
     */

    public function Rewind()
    {

        $this->Load();

        reset( $this->_Rows );

        return $this;

    }

    /**
     * Find first row and fill model or Clear it if no rows found
     * If no model is used it will return first row array or empty array
     *
     * @return Model|array
     */

    public function First()
    {

        $row = $this->RowByIndex(0);

        if ( ! $row && $this->ActiveDataSet() )  return $this->ActiveDataSet()->ResetToDefaults();

        else if ( ! $row ) return array();

        return $row;

    }

    /**
     * Find first row or returns false
     *
     * @return Model|array|bool
     */

    public function FirstOrFail()
    {
        return $this->RowByIndex(0);
    }

    /**
     * Find row by row ID first, if not found then by row index<br>
     * and return row ID or FALSE if not found
     *
     * @param $selector mixed row index or row ID to look for
     * @return bool|mixed returns row ID or FALSE if not found
     */

    public function FindRowID($selector)
    {

        if ( ! isset ( $this->_Indices['pk'] ) ) $this->IndexByPK();

        if ( isset( $this->_Rows[ $selector ] ) ) return $selector;

        return $this->FindRowIDByIndex( $selector );

    }

    /**
     * Find row id by row index
     *
     * @param $row_index
     * @return bool|mixed returns row ID or FALSE if not found
     */

    public function FindRowIDByIndex($row_index)
    {

        if ( ! is_int($row_index) ) return false;

        if ( ! isset ( $this->_Indices['pk'] ) ) $this->IndexByPK();

        if ( ! $this->_KeysIndex ) $this->_KeysIndex = array_keys( $this->_Rows );

        return isset( $this->_KeysIndex[ $row_index ] ) ? $this->_KeysIndex[ $row_index ] : false;

    }

    /**
     * Row by row index in rows array
     *
     * @param $row_index
     * @return Model|array|bool
     */

    public function RowByIndex($row_index)
    {
        return $this->Row( $this->FindRowIDByIndex( $row_index ) );
    }

    /**
     * Check if row exists by row id
     *
     * @param $row_id
     * @return bool
     */

    public function HasRow($row_id)
    {
        return isset( $this->_Rows[ $row_id ] );
    }

    /**
     * Moves data result pointer to the selected row in the result
     *
     * @param int $offset
     * @return bool TRUE on success or FALSE on fail
     */

    public function Seek($offset)
    {
        return $this->ActiveConnection()->Driver()->Seek( $this->Result(), $offset );
    }

}
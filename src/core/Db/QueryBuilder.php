<?php
/*
*-----------------------------------------------------------------------------------------------
* SQL QUERY	BUILDER     				  									    semiorbit.com
*-----------------------------------------------------------------------------------------------
*
*/

namespace Semiorbit\Db;

use Semiorbit\Config\Config;






class QueryBuilder
{


    protected $_QueryString;

    protected $_Dialect = 'mysql';

    protected $_Table;

    protected $_Cols = array();

    protected $_QueryChangedFlag = false;

    protected $_DirectSqlFlag = true;

    protected $_WhereString;

    protected $_OrderBy = array();

    protected $_Params = array();

    protected $_Offset;

    protected $_Limit;

    protected $_Page;


    protected function CFlag($set = true)
    {
        $this->_QueryChangedFlag = $set;

        return $this;
    }

    protected function DFlag($set = false)
    {
        $this->_DirectSqlFlag = $set;

        return $this;
    }

    /**
     * @param string $selector
     * @return QueryBuilder
     */

    public function From($selector)
    {

        $this->_Table = null;

        $this->_QueryString = null;

        $selector = trim( $selector );

        // If selector is table name in quotes

        if ( starts_with( $selector, '`' ) && ends_with( $selector, '`' ) )  { $this->DFlag()->CFlag()->_Table = trim( $selector, '` ' ); }

        // If selector string not contains spaces so definitely it is table name

        elseif ( ! strstr( $selector, ' ' ) ) { $this->DFlag()->CFlag()->_Table = $selector; }

        // If selector is sql query (DirectSqlFlag is turned on)

        else { $this->DFlag( true )->CFlag()->_QueryString = $selector; }


        return $this;

    }

    public function TableName()
    {
        return $this->_Table;
    }

    /**
     * Set columns in select statement
     *
     * @param array|string $columns
     * @return QueryBuilder
     */

    public function Select($columns = '*')
    {

        $this->DFlag()->CFlag()->_Cols = null;

        return $this->AndSelect( $columns );

    }

    /**
     * Append columns to select statement
     *
     * @param array|string $columns
     * @return QueryBuilder
     */

    public function AndSelect($columns = '*')
    {

        if ( empty( $columns ) || $columns == '*' || $columns == ['*'] )  { $this->CFlag()->_Cols = null; return $this; }

        $cols = [];

        if ( is_string( $columns ) ) {

            $cols = func_get_args();

        } else if ( is_array( $columns ) ) {

            $cols = $columns;

        }

        if ( ! is_array( $columns ) ) return $this;

        $cols = array_map('trim', $cols);

        if ( in_array( '*', $cols ) ) { $this->CFlag()->_Cols = null; return $this; }

        if (empty($this->_Cols)) $this->_Cols = array();

        $this->DFlag()->CFlag()->_Cols = array_merge( $this->_Cols, $cols );


        return $this;

    }

    public function Columns()
    {
        if ( empty( $this->_Cols ) ) $this->_Cols = ['*'];

        return $this->_Cols;
    }

    public function HasColumn($column)
    {
        return in_array( $column, $this->Columns() ) || in_array( '*', $this->Columns() );
    }

    public function OrderBy($field, $direction = 'ASC')
    {

        if ( is_empty( $field ) ) return $this;

        $direction = strtoupper( $direction );

        if ( ! in_array( $direction, [ 'ASC', 'DESC' ] ) ) $direction = 'ASC';

        $this->DFlag()->CFlag()->_OrderBy[ $field ] = $direction;

        return $this;
    }

    public function Limit($limit)
    {

        if ( empty( $limit ) ) return $this;

        if ( $limit <= 0 ) return $this;

        $limit = intval( $limit );

        $this->CFlag()->_Limit = $limit;

        return $this;

    }

    public function Offset($offset)
    {

        $offset = intval( $offset );

        if ( $offset < 0 ) return $this;

        $this->_Page = null;

        $this->CFlag()->_Offset = $offset;

        return $this;

    }

    public function Page($page)
    {

        $page = intval( $page );

        if ( $page <= 0 ) return $this;

        $this->_Offset = null;

        if ( $this->_Limit === null ) $this->_Limit = Config::RowsPerPage();

        $this->CFlag()->_Page = $page;

        return $this;

    }

    public function CurrentPage()
    {
        return $this->_Page;
    }

    public function RowsPerPage()
    {
        return $this->_Limit;
    }

    public function SelectString()
    {

        if ( is_empty( $this->_Table ) ) {

            if ( ! is_empty( $this->_QueryString ) ) return $this->_QueryString;

            else return null;

        }


        $cols = $this->Columns();


        if ( $cols == ['*'] ) $cols_string = '*';

        else $cols_string = implode( ', ', $cols );

        return "SELECT " . $cols_string . " FROM " . $this->TableName();

    }

    public function Where($where)
    {
        if ( is_string( $where ) ) $this->DFlag()->CFlag()->_WhereString = $where;

        return $this;
    }

    public function WhereString()
    {
        if ( empty( $this->_WhereString ) ) return '';

        return 'WHERE ' . $this->_WhereString;
    }

    public function OrderByString()
    {
        if ( empty( $this->_OrderBy ) ) return '';

        $order_arr = [];

        foreach ($this->_OrderBy as $fld => $dir) {

            $order_arr[] = $dir == 'ASC' ? $fld : $fld . ' ' . $dir;

        }


        $order_by_string = 'ORDER BY ' . implode( ', ', $order_arr );

        return $order_by_string;

    }


    public function LimitString()
    {

        if ( empty( $this->_Limit ) ) return '';

        if ( $this->_Page !== null )

            $this->_Offset = ($this->_Page - 1) * $this->_Limit;

        if ( $this->_Offset === null ) $this->_Offset = 0;

        $limit_string = "LIMIT {$this->_Offset}, {$this->_Limit}";

        return $limit_string;

    }

    public function QueryString()
    {
        $this->BuildQuery();

        return $this->_QueryString;
    }

    protected function BuildQuery()
    {

        if ( $this->_DirectSqlFlag ) {

            if ( $this->_QueryChangedFlag && ! empty( $this->_Limit ))

                $this->_QueryString = static::ClipLimit($this->_QueryString) . ' ' . $this->LimitString();

            return  $this->CFlag( false );

        } else if ( ! $this->_QueryChangedFlag ) return $this->CFlag( false );



        $select_string = $this->SelectString();

        //TODO This condition should be reconstruct after insert and update

        if ( empty( $select_string ) ) return $this->CFlag( false )->_QueryString = null;

        $query_parts[ 'select_string' ] = $select_string;

        $query_parts['where_string'] = $this->WhereString();

        $query_parts[ 'order_by_string' ] = $this->OrderByString();

        $query_parts[ 'limit_string' ] = $this->LimitString();

        $this->CFlag( false )->_QueryString = implode( ' ', $query_parts );

        return $this;

    }


    /**
     * @return array
     */
    public function Params()
    {
        return $this->_Params;
    }

    public function WithParams($params = [])
    {
        $this->_Params = $params;

        return $this;
    }


    public static function ClipLimit($sql)
    {
        //MYSQL LIMIT -------------------------------

        //Check if there is a limit string in sql

        $limit_pos = strripos( $sql, 'LIMIT' );

        if ($limit_pos) {

            //Check if the founded limit is not in a sub query

            $check_is_main_query_limit = preg_match( "/LIMIT \d+[ ,\d]*$/i", trim( substr( $sql, $limit_pos ) ));

            if ( $check_is_main_query_limit )

                $sql = substr( $sql, 0, $limit_pos );

        }

        return $sql;

    }

}
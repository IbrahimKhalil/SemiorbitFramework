<?php 
/*
 *-----------------------------------------------------------------------------------------------
* SEMIORBIT - DATASET > MODEL SUPER CLASS 		 					 semiorbit.com
*------------------------------------------------------------------------------------------------
*/
  
namespace Semiorbit\Data;




use Semiorbit\Component\Services;
use Semiorbit\Form\Form;
use Semiorbit\Db\DB;
use Semiorbit\Config\Config;
use Semiorbit\Db\Table;
use Semiorbit\Field\Control;
use Semiorbit\Http\Controller;
use Semiorbit\Output\TableView;
use Semiorbit\Support\Path;

/**
 * Class DataSet
 *
 * @package Semiorbit
 * @property Table _Table
 */

class DataSet extends Model
{

    const DEFAULT_CONTROLLER = null;


    protected $_Table;

    protected $_Controller;

    protected $_TableView;

    protected $_Name;



    protected static $_DefaultController;

    protected static $_SelectStatementFields;
    


    public function RenderForm($flush_output = true, $form_options = array())
    {

        ob_start();

        $show_form = true;

        $from_output = Form::UseDataSet($this)->Render(false, $form_options, function (DataSet $ds, $output) use (&$show_form) {


            # If ID field control is not none we need to re-check if the submitted id exists in database table,
            #  to make sure that current model should be treated as a new record or not.
            if ( isset ( $ds->ID->Control ) && $ds->ID->Control !=  Control::NONE ) $ds->DetectIsNew();


            if ($ds->IsNew()) {

                if ($ds->Policy()->AllowsRead()) {

                    $res = $ds->InsertRow();

                    $ds->onUserInsertedRow($res, $show_form, Config::ShowErrReport(), $output);

                }

            } else {

                if ($ds->Policy()->AllowsUpdate()) {

                    $res = $ds->UpdateRow();

                    $ds->onUserUpdatedRow($res, $show_form, Config::ShowErrReport(), $output);

                }

            }

        });


        if ($show_form) echo $from_output;

        $buffer = ob_get_contents();

        if ($flush_output) ob_flush();

        @ob_end_clean();
        return $buffer;

    }

    /**
     * @return TableView
     */

    public function ActiveTableView()
    {
        if ( ! $this->_TableView ) $this->UseTableView();

        return $this->_TableView;
    }

    public function UseTableView(TableView $table_view = null)
    {

        if ( ! $table_view ) $this->_TableView = new TableView();

        else $this->_TableView = $table_view;

        $this->_TableView->UseDataSet($this)->UseController( $this->ActiveController() );

        return $this;

    }

    /**
     * @return Controller|null
     */
    public function ActiveController()
    {
        return $this->_Controller;
    }

    public function UseController(Controller $controller)
    {
        $this->_Controller = $controller;

        return $this;
    }

    public function ErrorList()
    {

        $res = array();

        foreach ($this->Fields() as $k => $fld) :

            $res[$k] = $fld;

        endforeach;

        return $res;

    }


    /**
     * Create a new instance
     *
     * @return $this
     */

    public static function Create()
    {

        /** @var DataSet $myDataSet */

        $myDataSet = new static;

        $myDataSet->NewRecord();

        return $myDataSet;


    }

    /**
     * Select rows by defined order
     *
     * @param null $order_by
     * @return $this
     */

    public function OrderBy($order_by = null)
    {

        if ($order_by) $this->Table()->OrderBy($order_by);

        return $this;

    }

    /**
     * Create a new instance and prepare table to load selected rows by defined order
     *
     * @param $where
     * @param null $order_by
     * @return $this
     */

    public static function FilterBy($where, $order_by = null)
    {

        /** @var DataSet $myDataSet */

        $myDataSet = new static;

        $myDataSet->Table()->Where($where);

        if ($order_by) $myDataSet->Table()->OrderBy($order_by);


        return $myDataSet;


    }

    /**
     * Create an instance, and load row by id (from database)
     *
     * @param $id
     * @return $this returns dataset with selected row loaded, or new instance
     */

    public static function Load($id)
    {
        /** @var DataSet $myDataSet */

        $myDataSet = new static;

        $myDataSet->Read($id);

        return $myDataSet;
    }


    /**
     * Create an instance, and load row by id (from database) if exists
     *
     * @param $id
     * @return static returns dataset with selected row loaded, or <b>NULL</b> if row not found
     */

    public static function Find($id)
    {

        $myDataSet = static::Load($id);

        if ( $myDataSet->IsNew() ) return null;

        return $myDataSet;

    }


    /**
     * Create an instance, and load all rows (from database)
     *
     * @return $this returns dataset with selected row loaded, or new instance
     */

    public static function LoadAll()
    {

        /** @var DataSet $myDataSet */

        $myDataSet = new static;

        $myDataSet->Table()->Load();

        return $myDataSet;

    }


    /**
     * Get data table
     *
     * @return Table
     */

    public function Table()
    {

        if ( $this->_Table == null ) $this->_Table = $this->ActiveConnection()->Table( $this->TableName() )->UseDataSet( $this );

        return $this->_Table;

    }

    /**
     * Use a table as data source.<p>
     * This will <b>change</b> the dataset connection to <b>table active connection too.</b>
     *
     * @param Table $table
     * @return $this
     */

    public function UseTable(Table $table)
    {
        $this->UseConnection( $table->ActiveConnection() );

        $this->_Table = $table;

        $this->_Table->UseDataSet($this);

        return $this;
    }


    /**
     * Create a table instance using this model table name and connection
     *
     * @return Table
     */

    public static function All()
    {

        /** @var $myDataSet DataSet */

        $myDataSet = new static;

        return $myDataSet->Table()->UseDataSet( $myDataSet );

    }

    /**
     * Create a table instance using this model table name and connection then filter rows
     *
     * @param $where string sql where statement to filter data table
     * @return Table
     */

    public static function Where($where)
    {

        /** @var DataSet $myDataSet */

        $myDataSet = new static;

        return $myDataSet->Table()

                        ->UseDataSet( $myDataSet )->Where( $where );

    }

    /**
     * Fill model from first row or new row if no rows found
     *
     * @return static
     */

    public function First()
    {
        return $this->Table()->First();
    }


    /**
     * Fill model from first row or FALSE if no rows found
     *
     * @return Model|false
     */

    public function FirstOrFail()
    {
        return $this->Table()->FirstOrFail();
    }


    /**
     * Read data row from <u>database</u> by "ID" field then fill model fields. <p><p>
     * If selected "ID" <b>not found</b>, it will Reset the model as a <b>NEW record</b><p><p>
     * If "ID" is <b>NULL</b>, it will read <b>next</b> row in table <u>result</u>, if <b>no more rows</b> in <u>result</u> it will return <b>FALSE</b><p><p>
     *
     * NB: This will fire <b>onStart</b> event on load complete
     *
     * @param null $id
     * @return static returns this DataSet, or NULL if ID is NULL and no more rows in table result
     */

    public function Read($id = null)
    {

        if ( $id === null ) {

            $myRow = $this->Table()->Read();

            if ( ! $myRow ) return null;

            else if ( $this->Table()->ActiveDataSet() !== $this || $this->Table()->InstantiateDataSetEnabled() )

                $this->Fill( $myRow, false )->MarkAsNew(false);

            return $this;

        } else {

            return parent::Read($id);

        }

    }




    /**
     * Read row from LOADED rows in <u>Table</u> rows array (will not reload from database except if result not loaded yet)<p>
     * by ID field or row index <p><p>
     * If ID is <b>NULL</b>, it will read <b>next row</b> in Table rows array<p><p>
     *
     * NB: This will fire <b>onStart</b> event on load complete
     *
     *
     * @param $id mixed row id or row index in table
     * @return static Fills this model with data from selected row or return NULL
     */

    public function Row($id = null)
    {

        $myRow = $this->Table()->Row($id);

        if ( ! $myRow ) return null;

        else if ( $this->Table()->ActiveDataSet() !== $this || $this->Table()->InstantiateDataSetEnabled() )

            $this->Fill( $myRow, false )->MarkAsNew(false);

        return $this;

    }

    /**
     * Table loaded rows
     *
     * @return array
     */

    public function Rows()
    {
        return $this->Table()->Rows();
    }

    /**
     * Number of rows in "Table"
     *
     * @return int Returns rows array count
     */

    public function Count()
    {
        return $this->Table()->Count();
    }

    /**
     * Number of loaded rows from database to result set
     *
     * @return int Returns database selected rows count
     */

    public function RowCount()
    {
        return $this->Table()->RowCount();
    }


    /**
     * Number of all table rows in database
     *
     * @param $filter String Where statment to filter counting query result
     * @return int|null Returns count of all rows in database table
     */

    public static function CountAll($filter = null)
    {

        if ($filter != null) $filter = "WHERE {$filter}";

        $myDataSet = new static;

        return DB::Find( "SELECT COUNT({$myDataSet->ID->Name}) AS total_count FROM {$myDataSet->TableName()} {$filter}" );
    }

    public static function Has($id)
    {
        $myDataSet = new static;

        return DB::Find( "SELECT {$myDataSet->ID->Name} FROM {$myDataSet->TableName()} WHERE {$myDataSet->ID->Name} = '{$id}' LIMIT 1" );
    }

    /**
     * Create a new instance and load data table from selected source<p>
     * If selector is NULL then it will load all data table from database
     *
     * @param $selector string > Sql select statement
     *                  Object > SqlQuery
     *                  string > Table name
     *                  Object > Table
     *
     * @return static
     */

    public static function LoadFrom($selector = null)
    {

        /** @var $myDataSet DataSet */

        $myDataSet = new static;

        if ($selector instanceof Table) $myDataSet->UseTable($selector);

        else $myDataSet->Table()->From( $selector === null ? $myDataSet->TableName() : $selector )->UseDataSet( $myDataSet );

        return $myDataSet;

    }


    /**
     * Create a Database instance using this model table name and connection and then limit result
     *
     * @param int $rows_count
     * @return $this
     */

    public static function LoadFirst($rows_count = 1)
    {
        /** @var $myDataSet DataSet */

        $myDataSet = new static;

        $myDataSet->Table()->UseDataSet($myDataSet)->Limit($rows_count);

        return $myDataSet;

    }

    public static function LoadPage($page = 1, $rows_per_page = null)
    {
        /** @var $myDataSet DataSet */

        $myDataSet = new static;

        $myDataSet->Table()->UseDataSet($myDataSet)->Page($page)->Paginate($rows_per_page);

        return $myDataSet;

    }

    /**
     * Get data table pagination control
     *
     * @return \Semiorbit\Output\Pagination
     */

    public function Pagination()
    {
        return $this->Table()->Pagination();
    }

    /**
     * Enable pagination and set number of rows per page
     *
     * @param null $rows_per_page
     * @return $this
     */

    public function Paginate($rows_per_page = null)
    {
        $this->Table()->Paginate($rows_per_page);

        return $this;
    }

    /**
     * Set page number to load
     *
     * @param $page
     * @return $this
     */

    public function Page($page)
    {
        $this->Table()->Page($page);

        return $this;
    }

    /**
     * Limit result rows count
     *
     * @param int $rows_count
     * @return $this
     */

    public function Limit($rows_count)
    {
        $this->Table()->Limit($rows_count);

        return $this;
    }

    /**
     * Skip number of rows from the beginning of the result
     *
     * @param int $rows_count
     * @return $this
     */

    public function Offset($rows_count)
    {
        $this->Table()->Offset($rows_count);

        return $this;
    }

    /**
     * Remove all rows in dataset table one by one firing onRemove() event on each row removal
     *
     * @return int Count of deleted rows
     */

    public function RemoveRows()
    {

        $count = 0;

        while ($this->Read())
        {
            $res = $this->RemoveRow();

            if ($res) $count++;

        }

        return $count;

    }

    public static function DefaultController()
    {
        return static::$_DefaultController ?: static::DEFAULT_CONTROLLER;
    }

    public static function UseDefaultController($controller = 'Default')
    {
        static::$_DefaultController = $controller;
    }

    public function Name()
    {
        return $this->_Name ?: $this->_Name = Path::ClassShortName(static::class);
    }


    public function Namespace()
    {
        return Path::ClassNamespace(static::class);
    }


    public function setPackage($pkg)
    {
        $this->_Package = $pkg;

        return $this;
    }

    /**
     * Prepare model's fields list for sql select statement.<br/>
     *
     * @param bool|string $declare_table_name TRUE Prepend table name from model,<br/>
     *                                        FALSE Don't assign table name or <br/>
     *                                        <b>STRING</b> Set table name
     * @param bool $return_array
     * @param string $separator If separator is set to FALSE||NULL an array will be returned.<br/>
     *                           Otherwise comma separated list of fields select-expr or names wil be returned.
     * @return array|string
     */

    public static function ListFields($declare_table_name = true, $return_array = false, $separator = ', ')
    {
        return  (($declare_table_name && $return_array == false && $separator == ', ') && static::$_SelectStatementFields) ? static::$_SelectStatementFields :

            static::Create()->ListSelectStmtFields($declare_table_name, $return_array, $separator);
    }

}


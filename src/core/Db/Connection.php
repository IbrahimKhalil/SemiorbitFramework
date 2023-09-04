<?php
/*
*-----------------------------------------------------------------------------------------------
* Connection Management				  										    semiorbit.com
*-----------------------------------------------------------------------------------------------
*
*/

namespace Semiorbit\Db;




use Semiorbit\Base\Application;
use Semiorbit\Config\Config;
use Semiorbit\Db\Driver\Driver;
use Semiorbit\Support\AltaArray;


class Connection
{

    protected $_ConnectionID;

    protected $_Config;

    protected $_Driver;

    protected $_Connector;


    /**
     * Start connection to a database
     *
     * @param string $connection_id
     * @param array $con optional connection info otherwise connection info will be retrieved from config
     */

    public function __construct($connection_id, array $con = [])
    {
        $this->Connect($connection_id, $con);
    }

    /**
     * Open connection to a database
     *
     * @param string $connection_id
     * @param array $con optional connection info otherwise connection info will be retrieved from config
     * @return $this
     */

    public function Connect($connection_id, array $con = [])
    {

        $myCon = $con ?: Config::Connection($connection_id);


        if (empty($myCon))

            Application::Abort(503,  'Connection Failed!');


        /******************************************
         *
         * FIND CUSTOM DRIVER IN app\driver FOLDER
         *
         ******************************************/

        if (is_dir(BASEPATH . 'driver')) {

            $path = \Semiorbit\Component\Finder::LookFor(strtolower($myCon['driver']) . '.php', 'driver', true, true);

            if ($path) include_once "{$path['path']}";

            $driver_class = ucfirst($myCon['driver']);

        }


        // OR FIND DRIVER IN fw\Db\driver


        if (is_empty($driver_class)) {

            // isset($myCon['pdo_driver']) ? 'Pdo' : ...

            $class_name = ucfirst($myCon['driver'] ?? 'Pdo');

            $driver_class = 'Semiorbit\\Db\\Driver\\' . $class_name;

        }


        // CONNECT ---------------------------------------- >>

        /**@var $myDriver Driver */

        $myDriver = new $driver_class();

        $myDriver->Connect($myCon);

        $this->ActivateConnection($myCon['id'], $myDriver);

        //-------------------------------------------------- CONNECTED!


        return $this;

    }


    protected function ActivateConnection($connection_id, Driver $driver)
    {

        $this->_ConnectionID = $connection_id;

        $this->_Driver = $driver;

        $this->_Connector = $driver->Connector();

        $this->_Config = new \ArrayObject(!empty(Config::DbConnections()[$connection_id]) && is_string($connection_id) ? Config::DbConnections()[$connection_id] : array());

    }


    /**
     * Executes multiple queries
     *
     * @param string $sql
     * @return bool|int true/false or number of effected rows (driver dependent)
     */

    public function ExecuteAll(string $sql)
    {
        return $this->Driver()->ExecuteAll($sql);
    }


    /**
     * Executes unprepared single query returning bool|int
     * (no result object is returned)
     *
     * @param string $query
     * @return bool|int true/false or number of effected rows (driver dependent)
     */

    public function Exec(string $query)
    {
        return $this->Driver()->Exec($query);
    }

    /**
     * Executes a prepared or unprepared sql query returning a result object or boolean
     *
     * @param string $query
     * @param array $params
     * @return mixed
     */

    public function Execute(string $query, array $params = [])
    {
        return $this->Driver()->Execute($query, $params);
    }


    /**
     * <b>Execute</b> alias<br>
     * Executes a prepared or unprepared sql query returning a result object or boolean
     *
     * @param string $query
     * @param array $params
     * @return mixed
     */

    public function Cmd(string $query, array $params = [])
    {
        return $this->Driver()->Execute($query, $params);
    }

    /**
     * @param $selector string > Sql select statement
     *                  Object > SqlQuery
     *                  string > Table name
     *                  Object > Table
     *
     * @return Table
     */

    public function Table($selector)
    {

        $table = new Table( $selector );

        $table->UseConnection( $this );

        return $table;

    }

    /**
     * Fetches row from database.
     *
     * @param $query
     * @param array $params
     * @param string $class_name Object's class name, that will hold returned data.
     * @param array $constructor_params Parameters that should be sent on object construction.
     * @return AltaArray|object|null Data in AltaArray or other provided object with columns names as object properties.
     *                               If no data was found NULL will be returned.
     */

    public function Row($query, $params = [], $class_name = 'AltaArray', $constructor_params = [])
    {

        $result = $this->Cmd($query, $params);

        $row = $this->Driver()->FetchObject($result, $class_name, $constructor_params);

        return $row;

    }
    
    /**
     * Fetches row as an array from database.
     *
     * @param $query
     * @param array $params
     * @param int $result_type
     * @return array|null
     */

    public function RowArray($query, $params = [], $result_type = Driver::ROW_ASSOC)
    {

        $row = null;

        $result = $this->Cmd($query, $params);

        if ($result)

            $row = $this->Driver()->Fetch($result, $result_type);

        return $row;

    }

    /**
     * Returns value of a selected field from database, or NULL if it was not found.
     *
     * @param $query
     * @param array $params
     * @param int $field
     * @return object|null
     */

    public function Find($query, $params = [], $field = 0)
    {

        $result = $this->Cmd($query, $params);

        $row = $this->Driver()->FetchObject($result);

        return $row != null ? $row[$field] : null;

    }

    public function Has($query, $params = [])
    {
        $result = $this->Cmd($query, $params);

        $count = $this->Driver()->RowCount( $result );

        return $count;
    }

    /**
     * @param null $result
     * @return int
     */

    public function RowCount($result = null)
    {
        return $this->Driver()->RowCount( $result );
    }

    public function LastInsertId()
    {
        return $this->Driver()->LastInsertId();
    }

    public function Escape($value)
    {
        return $this->Driver()->Escape($value);
    }




	
	
	
	/* Connection Props */
	
	public function ConnectionID() 	{ return $this->_ConnectionID; }

    /**
     * @return \mysqli|\PDO
     */

    public function Connector() 	{ return $this->_Connector; }

    /**
     * @return Driver
     */

    public function Driver() 		{ return $this->_Driver; }
	
	public function Config() 		{ return $this->_Config; }
	
	
	
	/* Config Props */
	
	public function Host() 		    { return isset( $this->_Config['host'] ) ? $this->_Config['host'] : null; }
	
	public function User() 		    { return isset( $this->_Config['user'] ) ? $this->_Config['user'] : null; }
	
	public function Password() 	    { return isset( $this->_Config['password'] ) ? $this->_Config['password'] : null; }
	
	public function DB() 		    { return isset( $this->_Config['db'] ) ? $this->_Config['db'] : null; }
	
	public function Port() 		    { return isset( $this->_Config['port'] ) ? $this->_Config['port'] : null; }
	
	public function Socket() 	    { return isset( $this->_Config['socket'] ) ? $this->_Config['socket'] : null; }
	
	public function Persistent()    { return isset( $this->_Config['persistent'] ) ? $this->_Config['persistent'] : true; }
	
	
}
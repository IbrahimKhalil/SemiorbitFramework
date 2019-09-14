<?php
/*
*-----------------------------------------------------------------------------------------------
* Connection Management				  										    semiorbit.com
*-----------------------------------------------------------------------------------------------
*
*/

namespace Semiorbit\Db;




use Semiorbit\Config\CFG;
use Semiorbit\Db\Driver\Driver;
use Semiorbit\Debug\Log;
use Semiorbit\Support\AltaArray;


class Connection
{

    protected $_ConnectionID;

    protected $_Config;

    protected $_Driver;

    protected $_Connector;


    public function __construct($connection_id)
    {
        $this->Connect($connection_id);
    }

    /**
     * @param string $connection_id
     * @return $this
     */
    public function Connect($connection_id)
    {
        $myCon = CFG::Connections($connection_id);


        if (empty($myCon)) {

            Log::Inline()->TraceStartIndex(4)->Debug("Connection Failed!", $connection_id);

            die("<h3>Connection Failed!</h3>");

        }

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

            $class_name = isset($myCon['pdo_driver']) ? 'PDO' : ucfirst($myCon['driver']);

            $driver_class = 'Semiorbit\\Db\\Driver\\' . $class_name;

        }


        // CONNECT ---------------------------------------- >>

        $myDriver = new $driver_class($myCon['db'], $myCon['host'], $myCon['user'], $myCon['password'], $myCon['port'], $myCon['socket'], $myCon['persistent']);

        $this->ActivateConnection($myCon['id'], $myDriver);

        //-------------------------------------------------- CONNECTED!


        return $this;

    }


    protected function ActivateConnection($connection_id, Driver $driver)
    {

        $this->_ConnectionID = $connection_id;

        $this->_Driver = $driver;

        $this->_Connector = $driver->Connector();

        $this->_Config = new \ArrayObject(!empty(CFG::$Connections[$connection_id]) && is_string($connection_id) ? CFG::$Connections[$connection_id] : array());

    }


    public function Execute($query, $params = [])
    {
        return $this->Cmd($query, $params);
    }

    public function Cmd($query, $params = [])
    {

        $res = false;

        if (is_string($query)) {

            $res = $this->Driver()->Execute($query, $params);

        } elseif ($query instanceof QueryBuilder) {

            $res = $this->Driver()->Execute($query->QueryString(), $query->Params());

        }


        return $res;

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
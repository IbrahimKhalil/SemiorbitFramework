<?php
/*
*-----------------------------------------------------------------------------------------------
* DB ENGINE							  										    semiorbit.com
*-----------------------------------------------------------------------------------------------
*
*/

namespace Semiorbit\Db;






use Semiorbit\Db\Driver\Driver;

class DB
{
	
	protected static $_ConnectionID;
	
	protected static $_Connection;
	
	protected static $_ConnectionsPool = array();




    /**
     * @param string $connection_id
     * @return
     */

	public static function UseConnection($connection_id = null)
	{

		if ( ! empty( static::$_ConnectionID ) &&

            ( empty( $connection_id ) || $connection_id === static::$_ConnectionID ) )

            return static::ActiveConnection();


		// CONNECTION AVAILABLE IN POOL ? YES  
		
		if ( isset( static::$_ConnectionsPool [ $connection_id ] ) ) {
			
			static::ActivateConnection( static::$_ConnectionsPool [ $connection_id ] );
			
		} else {

            // : NOT FOUND IN POOL >> START CONNECTING

            $myCon = new Connection($connection_id);

            static::ActivateConnection($myCon);

        }


		return static::ActiveConnection();
	
	}

    /**
     * Returns connection from pool or create new connection if not found. <br>
     * <b>Notice:</b> new connection will not be added to pool unless added using <b>ActivateConnection</b> or <b>UseConnection</b>.
     *
     * @param string $connection_id
     * @return Connection
     */

    public static function Connection($connection_id = null): Connection
    {

        if ( ! empty( static::$_ConnectionID ) && ( empty( $connection_id ) || $connection_id === static::$_ConnectionID ) )

            return static::ActiveConnection();

        // CONNECTION AVAILABLE IN POOL ? YES
        // : NOT FOUND IN POOL >> START CONNECTING

        return static::$_ConnectionsPool [$connection_id] ?? new Connection($connection_id);

    }

    /**
     * Returns connection from pool or <b>create a new one without adding to pool</b>
     *
     * @param string|array $connection connection id OR connection info array
     * @return Connection
     */

    public static function WithConnection($connection = null)
    {
        return is_array($connection) ? new Connection(uniqid(), $connection) : static::Connection($connection);
    }


    /**
     * Open SQLite database connection, and <b>add it to pool</b> using database name as connection_id, without activating it.<br>
     * Using SQLite3 driver
     *
     * @param string $db
     * @param array $options
     * @param string $encryption
     * @return Connection
     */
    public static function WithSqlite(string $db, array $options = [], string $encryption = ''): Connection
    {

        if (isset( static::$_ConnectionsPool [$db] )) return static::$_ConnectionsPool [$db];


        $con = new Connection($db, [

            'id' => $db,

            'driver' => 'sqlite3',

            'db' => $db,

            'password' => $encryption,

            'options' => $options

        ]);

        static::$_ConnectionsPool [ $con->ConnectionID() ] = $con;

        return $con;

    }

    /**
     * Add connection to pool and activate it
     *
     * @param Connection $con
     * @return void
     */

    protected static function ActivateConnection(Connection $con)
	{
		
		static::$_Connection = $con;
		
		static::$_ConnectionID = $con->ConnectionID();
		
		static::$_ConnectionsPool [ $con->ConnectionID() ] = $con;
		
	}
	
	
	
	/**
	 * @return Connection
	 */

	public static function ActiveConnection(): Connection
    {
		if ( empty( static::$_Connection ) ) static::UseConnection();

		return static::$_Connection;	
	}
	
	public static function ActiveConnectionID()
	{
		if ( empty( static::$_ConnectionID ) ) static::UseConnection();


		return static::$_ConnectionID;
	}


	public static function Close($connection_id = null)
	{
		//if ( empty( $connection_id ) ) $connection_id = static::$_ConnectionID;
		
		
	}
	
	public static function CloseAll()
	{
		
	}
	
	public static function Reconnect($connection_id = null)
	{
		if ( empty( $connection_id ) ) $connection_id = static::$_ConnectionID;
		
		static::Close( $connection_id );
		
		//static::Connect( $connection_id );
	}



    /**
     * Executes multiple queries
     *
     * @param string $sql
     * @return bool|int true/false or number of effected rows (driver dependent)
     */

    public static function ExecuteAll(string $sql)
    {
        return static::ActiveConnection()->ExecuteAll($sql);
    }


    /**
     * Executes a prepared or unprepared sql query returning a result object or boolean
     *
     * @param string $query
     * @param array $params
     * @return mixed
     */

    public static function Execute(string $query, array $params = [])
    {
        return static::ActiveConnection()->Execute( $query, $params );
    }


    /**
     * Executes unprepared single query returning bool|int
     * (no result object is returned)
     *
     * @param string $query
     * @return bool|int true/false or number of effected rows (driver dependent)
     */

    public static function Exec(string $query)
    {
        return static::ActiveConnection()->Exec($query);
    }


    /**
     * <b>Execute</b> alias<br>
     * Executes a prepared or unprepared sql query returning a result object or boolean
     *
     * @param string $query
     * @param array $params
     * @return mixed
     */

    public static function Cmd(string $query, array $params = [])
    {
        return static::ActiveConnection()->Cmd( $query, $params );
    }

    /**
     * @param $selector string > Sql select statement
     *                  Object > SqlQuery
     *                  string > Table name
     *                  Object > Table
     *
     * @return Table
     */

    public static function Table($selector)
    {
        return static::ActiveConnection()->Table( $selector );
    }

    public static function Row($query, $params = [], $class_name = 'AltaArray', $constructor_params = [] )
    {
        return static::ActiveConnection()->Row( $query, $params, $class_name, $constructor_params );
    }
    
    public static function RowArray($query, $params = [], $result_type = Driver::ROW_ASSOC )
    {
        return static::ActiveConnection()->RowArray( $query, $params, $result_type );
    }

    public static function Find($query, $params = [], $field = 0)
    {
        return static::ActiveConnection()->Find( $query, $params, $field );
    }

    /**
     * @param null $result
     * @return int
     */

    public static function RowCount($result = null)
    {
        return static::ActiveConnection()->Driver()->RowCount( $result );
    }

    public static function LastInsertId()
    {
        return static::ActiveConnection()->Driver()->LastInsertId();
    }


	public static function Escape($value)
	{
        return static::ActiveConnection()->Driver()->Escape($value);
	}


    public static function BeginTransaction()
    {
        return  static::ActiveConnection()->Driver()->BeginTransaction();
    }

    public static function Commit()
    {
        return  static::ActiveConnection()->Driver()->Commit();
    }

    public static function Rollback()
    {
        return  static::ActiveConnection()->Driver()->Rollback();
    }
	
}
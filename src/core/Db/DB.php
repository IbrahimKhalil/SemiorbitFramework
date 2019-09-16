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
	
	protected static $_DBInstance;
	
	
	
	protected static function DBInstance()
	{

		if ( static::$_DBInstance === null )	static::$_DBInstance = new static();
	
		return static::$_DBInstance;
	
	}

    /**
     * @param string $connection_id
     * @return DB
     */

	public static function UseConnection($connection_id = null)
	{
		
		if ( ! empty( static::$_ConnectionID ) && ( empty( $connection_id ) || $connection_id === static::$_ConnectionID ) ) return static::DBInstance(); 
	
		// CONNECTION AVAILABLE IN POOL ? YES  
		
		if ( isset( static::$_ConnectionsPool [ $connection_id ] ) ) {
			
			static::ActivateConnection( static::$_ConnectionsPool [ $connection_id ] );
			
			return static::DBInstance();
			
		}
		
		// : NOT FOUND IN POOL >> START CONNECTING

	
		$myCon = new Connection($connection_id); 
		
		static::ActivateConnection( $myCon );


		
		return static::DBInstance();
	
	}

    /**
     * @param string $connection_id
     * @return Connection
     */

    public static function Connection($connection_id = null)
    {

        if ( ! empty( static::$_ConnectionID ) && ( empty( $connection_id ) || $connection_id === static::$_ConnectionID ) ) return static::ActiveConnection();

        // CONNECTION AVAILABLE IN POOL ? YES

        if ( isset( static::$_ConnectionsPool [ $connection_id ] ) ) {

            return static::$_ConnectionsPool [ $connection_id ] ;


        }

        // : NOT FOUND IN POOL >> START CONNECTING

        $myCon = new Connection($connection_id);

        return $myCon;

    }

    /**
     * @param string $connection_id
     * @return Connection
     */

    public static function WithConnection($connection_id = null)
    {
        return static::Connection($connection_id);
    }
	
	protected static function ActivateConnection(Connection $con)
	{
		
		static::$_Connection = $con;
		
		static::$_ConnectionID = $con->ConnectionID();
		
		static::$_ConnectionsPool [ $con->ConnectionID() ] = $con;
		
	}
	
	
	
	/**
	 * @return Connection
	 */

	public static function ActiveConnection()
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

    public static function Execute($query, $params = [])
    {
        return static::ActiveConnection()->Cmd( $query, $params );
    }

    public static function Cmd($query, $params = [])
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
<?php
/*
 *-----------------------------------------------------------------------------------------------
* DB DRIVER	INTERFACE				  										    semiorbit.com
*-----------------------------------------------------------------------------------------------
*
*/

namespace Semiorbit\Db\Driver;





interface Driver
{

    const ROW_ASSOC = 1;

	const ROW_NUM = 2;

    const ROW_BOTH = 3;



	public function Connect(array $con);

    public function Connector();

    public function Prepare($query, $params);


    /**
     * Executes a prepared or unprepared query query returning a result object or boolean
     *
     * @param string $query
     * @param array $params
     * @return mixed
     */

    public function Execute(string $query, array $params = []);


    /**
     * Executes multiple queries
     *
     * @param string $sql
     * @return mixed
     */
    public function ExecuteAll(string $sql);


    /**
     * Executes unprepared single query returning bool|int
     * (no result object is returned)
     *
     * @param string $query
     * @return bool|int true/false or number of effected rows (driver dependent)
     */

    public function Exec(string $query);

    public function Fetch($result, $result_type = Driver::ROW_BOTH);

    public function FetchObject( $result, $class_name = 'AltaArray', $constructor_params = [] );

    public function FetchAll($result, $result_type = Driver::ROW_ASSOC);

    public function FetchAllObject($result, $class_name = 'AltaArray', $constructor_params = [] );

    /**
     * Moves the result pointer to selected row in the result
     *
     * @param $result
     * @param $offset
     * @return bool
     */
    public function Seek($result, $offset);

    /**
     * @param null $result
     * @return int
     */

    public function RowCount($result = null);

    public function LastInsertId();

    public function Escape($value);

    /**
     * Generates a unique id using a server function
     *
     * @param bool|string $value TRUE or Server function as string e.g. "UUID_SHORT()"
     * @return bool
     */

    public function UniqueId($value = true);


    public function BeginTransaction();

    public function Commit();

    public function Rollback();


    public function FreeResult($result);


    public function ErrorInfo();


    /**
     * Returns driver type like [in lowercase, no spaces]: 'mysql', 'sqlite', 'sqlserver', 'oracle', 'postgresql'
     *
     * @return string
     */
    public function DriverManagementSystem() : string;

	
}
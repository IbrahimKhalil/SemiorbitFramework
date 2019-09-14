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



	public function Connect($db, $host = 'localhost', $user = 'root', $password = '', $port = null, $socket = null);

    public function Connector();

    public function Prepare($sql, $params);

    public function Execute($sql, $params = []);

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
     * @param bool|string $value TRUE or Server function as string eg. "UUID_SHORT()"
     * @return bool
     */

    public function UniqueId($value = true);


    public function BeginTransaction();

    public function Commit();

    public function Rollback();
	
}

?>
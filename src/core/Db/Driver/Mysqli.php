<?php
/*
*-----------------------------------------------------------------------------------------------
* MYSQLI DRIVER						  										    semiorbit.com
*-----------------------------------------------------------------------------------------------
*
*/

namespace Semiorbit\Db\Driver;





use Semiorbit\Support\AltaArray;
use Semiorbit\Support\AltaArrayKeys;
use Semiorbit\Debug\Log;


class Mysqli implements Driver
{

    public $Host = 'localhost';        //Server Name

    public $User = 'root';            //Database User Name

    public $Password = '';

    public $Database = '';

    public $Port = null;

    public $Socket = null;

    public $CharSet = 'utf8';

    public $Collation = 'utf8_general_ci';

    public $Persistent = true;


    public $_Connector;


    public function __construct($db, $host = 'localhost', $user = 'root', $password = '', $port = null, $socket = null, $persistent = true)
    {
        $this->Connect($db, $host, $user, $password, $port, $persistent);
    }


    public function Connect($db, $host = 'localhost', $user = 'root', $password = '', $port = null, $socket = null, $persistent = true)
    {

        $this->Host = $host;

        $this->User = $user;

        $this->Password = $password;

        $this->Database = $db;

        $this->Port = $port;

        $this->Socket = $socket;

        $this->Persistent = $persistent;


        if ($this->Persistent) $this->Host = 'p:' . $this->Host;

        $myCon = new \mysqli($this->Host, $this->User, $this->Password, $this->Database, $this->Port, $this->Socket);


        /* check connection */

        if ($myCon->connect_errno) {

            Log::Inline()->Trace(0)->Info("Connection Failed!", sprintf("Connection Failed: %s\n", $myCon->connect_error));

            die ("<h3>Connection Failed!</h3>");

        }


        $myCon->query("SET NAMES '{$this->CharSet}'");

        $myCon->query("SET CHARACTER SET {$this->CharSet}");


        $this->_Connector = $myCon;


        return $myCon;

    }

    /**
     * @param $sql string
     * @param $params array
     * @return \mysqli_stmt
     */

    public function Prepare($sql, $params)
    {
        $stmt = $this->Connector()->prepare($sql);

        return $stmt;
    }


    /**
     * @param $sql
     * @param array $params
     * @return bool|\mysqli_result
     */

    public function Execute($sql, $params = [])
    {

        if (empty($params)) {

            $result = $this->Connector()->query($sql);

        } else {

            $stmt = $this->Prepare($sql, $params);

            if (!$stmt) return false;

            $result = $stmt->execute();

            $result_set = $stmt->get_result(); //TODO: BIND FOR NON MYSQLND

            if ($result_set) {

                $result = $result_set;

                $stmt->free_result();
            }

            $stmt->close();

        }



        return $result;

    }

    /**
     * @param \mysqli_result $result
     * @param int $result_type
     * @return mixed
     */

    public function Fetch($result, $result_type = Driver::ROW_BOTH)
    {

        if (!$result instanceof \mysqli_result) return null;

        //dd('d');

        //dd($result->)

        //dd($result);

        $row = $result->fetch_array($result_type);

        return $row;

    }

    /**
     * @param \mysqli_result $result
     * @param string $class_name
     * @param array $constructor_params
     * @return null|object
     */

    public function FetchObject($result, $class_name = 'AltaArray', $constructor_params = [])
    {

        if (!$result instanceof \mysqli_result) return null;

        $obj = null;

        if ($class_name == 'AltaArray') {

            $row = $this->Fetch($result);

            if ($row) $obj = new AltaArray($row, AltaArrayKeys::CASE_SENSITIVE);

        } else {

            $obj = $result->fetch_object($class_name, $constructor_params);
        }

        return $obj;

    }

    /**
     * @param \mysqli_result $result
     * @param int $result_type
     * @return array
     */

    public function FetchAll($result, $result_type = Driver::ROW_ASSOC)
    {

        if ( ! $result instanceof \mysqli_result ) return null;

        if ( ! $result ) return null;

        $result->data_seek(0);


        if ( method_exists( $result, 'fetch_all' ) ) {

            $tbl = $result->fetch_all($result_type);

        }else {

            $tbl = array();

            while ($row = $result->fetch_assoc()) {

                $tbl[] = $row;

            }

        }

        return $tbl;

    }

    /**
     * @param $result
     * @param string $class_name
     * @param array $constructor_params
     * @return array
     */

    public function FetchAllObject($result, $class_name = 'AltaArray', $constructor_params = [])
    {

        if (!$result instanceof \mysqli_result) return null;

        $tbl = [];

        while ($row = $this->FetchObject($result, $class_name, $constructor_params)) {

            $tbl[] = $row;

        }

        return $tbl;

    }

    /**
     * Moves the result pointer to selected row in the result
     *
     * @param $result
     * @param $offset
     * @return bool
     */

    public function Seek($result, $offset)
    {
        if (!$result instanceof \mysqli_result) return false;

        return $result->data_seek($offset);
    }

    /**
     * @param null $result
     * @return int
     */

    public function RowCount($result = null)
    {

        if ($result === false) return 0;

        if ($result instanceof \mysqli_result) return $result->num_rows;

        else return $this->Connector()->affected_rows;

    }


    /**
     * @return mixed
     */

    public function LastInsertId()
    {
        return $this->Connector()->insert_id;
    }


    /**
     * @return \mysqli
     */

    public function Connector()
    {
        return $this->_Connector;
    }

    public function Escape($value)
    {
        return $this->Connector()->real_escape_string($value);
    }

    /**
     * Generates a unique id using a server function
     *
     * @param bool|string $value TRUE or Server function as string eg. "UUID_SHORT()"
     * @return bool
     */

    public function UniqueId($value = true)
    {

        $func = is_string($value) ? $value : 'UUID()';

        $result = $this->Connector()->query("SELECT {$func}")->fetch_array();

        return  isset($result[0]) ? $result[0] : false;

    }

    public function BeginTransaction()
    {
        return $this->Connector()->begin_transaction();
    }

    public function Commit()
    {
        return $this->Connector()->commit();
    }

    public function Rollback()
    {
        return $this->Connector()->rollback();
    }


}
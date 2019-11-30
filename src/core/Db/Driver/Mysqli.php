<?php
/*
*-----------------------------------------------------------------------------------------------
* MYSQLI DRIVER						  										    semiorbit.com
*-----------------------------------------------------------------------------------------------
*
*/

namespace Semiorbit\Db\Driver;






use Semiorbit\Base\Application;
use Semiorbit\Field\DataType;
use Semiorbit\Field\Field;
use Semiorbit\Support\AltaArray;
use Semiorbit\Support\AltaArrayKeys;


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

    protected $_Stmts;


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

        mysqli_report(MYSQLI_REPORT_STRICT );


        $myCon = null;

        $myCon = new \mysqli($this->Host, $this->User, $this->Password, $this->Database, $this->Port, $this->Socket);



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

        if (! (!is_assoc($params) &&

            isset($params[0]) && (!is_array($params[0])) &&

            preg_match("#[sibd]{1,".(count($params)-1)."}#i", $params[0])))


            [$sql, $params] = $this->ExtractParamsArray($sql, $params);


        $sql_hash = md5($sql);

        $stmt = isset($this->_Stmts[$sql_hash]) ? $this->_Stmts[$sql_hash] :

            $this->_Stmts[$sql_hash] = $this->Connector()->prepare($sql);

        if (! $stmt)

            Application::Abort(503, "Prepare sql statement failed: {$sql}");

        $stmt->bind_param(...$params);

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

             $result_set = $stmt->get_result();

            if ($result_set) {

                $result = $result_set;

                $stmt->free_result();
            }

            //$stmt->close();

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


    protected function ExtractParamsArray($sql, array $params)
    {

        $named_parameters = false;

        $type_string = "";

        $pms = [];

        $types = [];


        foreach ($params as $name => $value) {


            // Is Named?

            if (!is_numeric($name))

                $named_parameters = true;



            if (is_array($value)) {

                // Typed

                $types[$name] = isset($value[1]) ? $this->MapType($value[1]) : "s";

                $pms[$name] = $value[0] ?? null;


            } elseif ($value instanceof Field) {

                // Typed from Field object

                $types[$name] = $this->MapType($value->Type) ?: "s";

                $pms[$name] = $value->Value;

            } else {

                // Simple

                $types[$name] = "s";

                $pms[$name] = $value;

            }

        }

        if ($named_parameters)

            [$sql, $pms, $types] = $this->PrepareNamedParameters($sql, $pms, $types);


        $type_string = implode('', $types);

        array_unshift($pms, $type_string);


        return [$sql, $pms];

    }


    protected function PrepareNamedParameters($sql, array $params, array $types)
    {

        $pms = [];

        $ordered_types = [];

        $sql = preg_replace_callback("#:(\w+)#ui", function ($matches) use ($params, $types, &$pms, &$ordered_types) {


            if ( isset($matches[1]) && array_key_exists($matches[1], $params) ) {

                $pms[] = $params[$matches[1]];

                $ordered_types[] = $types[$matches[1]];

                return '?';

            }

            return $matches[0];

        }, $sql);

        return [$sql, $pms, $ordered_types];

    }


    public function MapType($data_type)
    {

        if (in_array($data_type, ["i", "s", "d", "b"])) return $data_type;

        if (in_array($data_type, [DataType::INT, DataType::BOOL])) return "i";

        elseif (in_array($data_type, [DataType::FLOAT, DataType::DOUBLE])) return "d";

        elseif (in_array($data_type, [DataType::BINARY])) return "b";

        else return "s";



    }

}
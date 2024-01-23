<?php
/*
*-----------------------------------------------------------------------------------------------
* MYSQLI DRIVER						  										    semiorbit.com
*-----------------------------------------------------------------------------------------------
*
*/

namespace Semiorbit\Db\Driver;






use Semiorbit\Base\Application;
use Semiorbit\Config\Config;
use Semiorbit\Debug\FileLog;
use Semiorbit\Field\DataType;
use Semiorbit\Field\Field;
use Semiorbit\Support\AltaArray;
use Semiorbit\Support\AltaArrayKeys;


class Mysqli implements Driver
{

    public $Host = 'localhost';        //Server FullName

    public $User = 'root';            //Database User FullName

    public $Password = '';

    public $Database = '';

    public $Port = null;

    public $Socket = null;

    public $CharSet = 'utf8';

    public $Collation = 'utf8_general_ci';

    public $Persistent = true;


    public $_Connector;

    protected $_Stmts;

    private array $_CashedErrorList = [];


    public function __construct($db = null, $host = 'localhost', $user = 'root', $password = '', $port = null, $socket = null, $persistent = true)
    {
        if ($db) $this->Connect(['db' => $db, 'host' => $host, 'user' => $user, 'password' => $password, 'port' => $port, 'socket' => $socket, 'persistent' => $persistent]);
    }


    public function Connect(array $con)
    {

        $this->Host = $con['host'];

        $this->User = $con['user'];

        $this->Password = $con['password'];

        $this->Database = $con['db'];

        $this->Port = $con['port'];

        $this->Socket = $con['socket'];

        $this->Persistent = $con['persistent'];


        if ($this->Persistent) $this->Host = 'p:' . $this->Host;

        mysqli_report(MYSQLI_REPORT_STRICT );



        $myCon = new \mysqli($this->Host, $this->User, $this->Password, $this->Database, $this->Port, $this->Socket);



        $myCon->query("SET NAMES '{$this->CharSet}'");

        $myCon->query("SET CHARACTER SET {$this->CharSet}");



        $this->_Connector = $myCon;


        return $myCon;

    }


    /**
     * @param $query string
     * @param $params array
     * @return \mysqli_stmt
     */

    public function Prepare($query, $params)
    {

        if (! (!is_assoc($params) &&

            isset($params[0]) && (!is_array($params[0])) &&

            preg_match("#[sibd]{1,".(count($params)-1)."}#i", $params[0])))


            [$query, $params] = $this->ExtractParamsArray($query, $params);


        $sql_hash = md5($query);

        $stmt = isset($this->_Stmts[$sql_hash]) ? $this->_Stmts[$sql_hash] :

            $this->_Stmts[$sql_hash] = $this->Connector()->prepare($query);

        if (! $stmt)

            Application::Abort(503, "Prepare query statement failed: {$query}");


        $stmt->bind_param(...$params);

        return $stmt;

    }


    /**
     * @param string $query
     * @param array $params
     * @return bool|\mysqli_result
     */

    public function Execute(string $query, array $params = [])
    {

        if (empty($params)) {

            $result = $this->Connector()->query($query);

        } else {

            $stmt = $this->Prepare($query, $params);

            if (!$stmt) return false;

            $result = $stmt->execute();

             $result_set = $stmt->get_result();

            if ($result_set) {

                $result = $result_set;

                $stmt->free_result();
            }

            //$stmt->close();

        }


        if (Config::DebugMode()) {

            $dbLog = new FileLog('db_log', true);

            $dbLog->Log(FileLog::DEBUG, "904", "[MYSQLI@QUERY]", $query);

            $dbLog->Log(FileLog::DEBUG, "904", "[MYSQLI@PARAMS]", json_encode($params, JSON_UNESCAPED_UNICODE));

        }



        return $result;

    }

    /**
     * Execute multiple queries
     *
     * Returns true/false
     *
     * @param string $sql
     * @return false|array
     */

    /**
     * Returns array of mysqli_result sets
     * or false if failed
     * or null if partially (some queries) failed
     * NB. FreeResult should be called after executing multi_query
     *
     * @param string $sql
     * @return array|bool|null
     */

    public function ExecuteAll(string $sql)
    {


        $exec = $this->Connector()->multi_query($sql);

        $list_result = [];

        $this->_CashedErrorList = [];

        if ($exec) {

            do {

                // Store the current result set

                $result = $this->Connector()->store_result();

                // Check for errors in the current result set

                if ($result instanceof \mysqli_result) {

                    $list_result[] = $result;

                } else if ($this->Connector()->errno) {

                    array_push($this->_CashedErrorList, ...$this->Connector()->error_list);

                    $exec = null;

                    break; // Exit the loop in case of an error
                }

                // Move to the next result set

            } while ($this->Connector()->more_results() && $this->Connector()->next_result());


            if ($this->Connector()->errno && $exec) return null;

            return $exec && $list_result ? $list_result : $exec;

        }

        return $exec;

    }

    /**
     * Execute a single query returning true/false
     *
     * Returns true/false or number of effected rows (PDO)
     *
     * @param string $query
     * @return bool
     */

    public function Exec(string $query)
    {
        return $this->Connector()->real_query($query);
    }


    /**
     * @param \mysqli_result $result
     * @param int $result_type
     * @return mixed
     */

    public function Fetch($result, $result_type = Driver::ROW_BOTH)
    {

        if (!$result instanceof \mysqli_result) return null;

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

        $result->free_result();

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

        $result->free_result();

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
     * @param bool|string $value TRUE or Server function as string e.g. "UUID_SHORT()"
     * @return bool
     */

    public function UniqueId($value = true)
    {

        $func = is_string($value) ? $value : 'UUID()';

        $result = $this->Connector()->query("SELECT {$func}")->fetch_array();

        return $result[0] ?? false;

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

        elseif (in_array($data_type, [DataType::BINARY])) return "s";

        else return "s";

    }

    public function DriverManagementSystem(): string
    {
        return 'mysql';
    }

    public function FreeResult($result)
    {

        if ($result instanceof \mysqli_result) $result->free_result();

        else {

            // free result for multi_query

            do {

                // Store the current result set

                $resultSet = $this->Connector()->store_result();

                // Free the current result set

                if ($resultSet instanceof \mysqli_result) $resultSet->free();

            } while ($this->Connector()->more_results() && $this->Connector()->next_result());


        }

    }

    public function ErrorInfo()
    {
        return [...$this->_CashedErrorList, ...$this->Connector()->error_list];
    }

}
<?php /** @noinspection PhpComposerExtensionStubsInspection */

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
use Semiorbit\Support\AltaArray;
use Semiorbit\Support\AltaArrayKeys;
use Semiorbit\Support\Path;


class Sqlite3 implements Driver
{


    public $EncryptionKey = null;

    public $Database = '';

    public $Flags = null;

    public $Encoding = 'UTF-8';

    public $Collation = '';


    public $_Connector;

    protected $_Stmts;


    public function __construct($db = null, $flags = SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE,  $encryption_key = null, $encoding = 'UTF-8')
    {

        if ($db) $this->Connect([

            'db' => $db,

            'password' => $encryption_key,

            'options' => [

                'flags' => $flags,

                'encoding' => $encoding
            ]

        ]);

    }


    public function Connect(array $con)
    {


        $this->EncryptionKey = $con['password'];

        $this->Database = $con['db'];

        $this->Flags = $con['options']['flags'] ?? SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE;

        $this->Encoding = $con['options']['encoding'] ?? 'UTF-8';


        $this->Database = Path::IsAbsolute($this->Database) ? $this->Database :

            Application::DatabasePath( $this->Database);


        $myCon = new \SQLite3($this->Database, $this->Flags, $this->EncryptionKey);



        $myCon->exec("PRAGMA encoding = '{$this->Encoding}';");

        $myCon->exec("PRAGMA case_sensitive_like = false");



        $this->_Connector = $myCon;


        return $myCon;

    }


    /**
     * @param $query string
     * @param $params array
     * @return \SQLite3Stmt
     */

    public function Prepare($query, $params)
    {


        $sql_hash = md5($query);

        $stmt = $this->_Stmts[$sql_hash] ?? $this->_Stmts[$sql_hash] = $this->Connector()->prepare($query);

        if (! $stmt)

            Application::Abort(503, "Prepare query statement failed: {$query}");


        foreach ($params as $param => $value)

            $stmt->bindValue($param, $value);


        return $stmt;

    }


    /**
     * @param string $query
     * @param array $params
     * @return bool|\SQLite3Result
     */

    public function Execute(string $query, array $params = [])
    {

        if (empty($params)) {

            $result = $this->Connector()->query($query);

        } else {

            $stmt = $this->Prepare($query, $params);

            if (!$stmt) return false;

            $result = $stmt->execute();

            //$stmt->close();

        }


        if (Config::DebugMode()) {

            $dbLog = new FileLog('db_log', true);

            $dbLog->Log(FileLog::DEBUG, "904", "[SQLITE3@QUERY]", $query);

            $dbLog->Log(FileLog::DEBUG, "904", "[SQLITE3@PARAMS]", json_encode($params, JSON_UNESCAPED_UNICODE));

        }



        return $result;

    }


    /**
     * Execute multiple queries
     *
     * Returns true/false
     *
     * @param string $sql
     * @return bool
     */

    public function ExecuteAll(string $sql)
    {
        return $this->Connector()->exec($sql);
    }


    /**
     * Execute a result-less single query
     *
     * Returns true/false or number of effected rows (PDO)
     *
     * @param string $query
     * @return bool
     */

    public function Exec(string $query)
    {
        return $this->Connector()->exec($query);
    }





    /**
     * @param \SQLite3Result $result
     * @param int $result_type
     * @return mixed
     */

    public function Fetch($result, $result_type = Driver::ROW_BOTH)
    {

        if (!$result instanceof \SQLite3Result) return null;

        $row = $result->fetchArray($result_type);

        return $row;

    }

    /**
     * @param \SQLite3Result $result
     * @param string $class_name
     * @param array $constructor_params
     * @return null|object
     */

    public function FetchObject($result, $class_name = 'AltaArray', $constructor_params = [])
    {

        if (!$result instanceof \SQLite3Result) return null;

        $obj = null;

        if ($class_name == 'AltaArray') {

            $row = $this->Fetch($result);

            if ($row) $obj = new AltaArray($row, AltaArrayKeys::CASE_SENSITIVE);

        } else {

            $obj = $result->fetchArray();
        }

        return $obj;

    }

    /**
     * @param \SQLite3Result $result
     * @param int $result_type
     * @return array
     */

    public function FetchAll($result, $result_type = Driver::ROW_ASSOC)
    {

        if ( ! $result instanceof \SQLite3Result) return null;

        if ( ! $result ) return null;


        $result->reset();

        $tbl = array();

        while ($row = $result->fetchArray($result_type)) {

            $tbl[] = $row;

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

        if (!$result instanceof \SQLite3Result) return null;

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

        if (!$result instanceof \SQLite3Result) return false;

        $result->reset();

        $i = 0;

        do {

            if ($i > $offset -1) break;

        } while ($result->fetchArray());

        return true;

    }

    /**
     * @param null $result
     * @return int
     */

    public function RowCount($result = null)
    {

        if ($result === false) return 0;

        if ($result instanceof \SQLite3Result) return count($this->FetchAll($result));

        else return $this->Connector()->changes();

    }


    /**
     * @return mixed
     */

    public function LastInsertId()
    {
        return $this->Connector()->lastInsertRowID();
    }


    /**
     * @return \SQLite3
     */

    public function Connector()
    {
        return $this->_Connector;
    }

    public function Escape($value)
    {
        return $this->Connector()->escapeString($value);
    }

    /**
     * Generates a unique id using php "uniqid" function
     *
     * @param bool|string $value always TRUE
     * @return bool
     */

    public function UniqueId($value = true)
    {
        return  uniqid('', true);
    }

    public function BeginTransaction()
    {
        return $this->Connector()->exec('BEGIN;');
    }

    public function Commit()
    {
        return $this->Connector()->exec('COMMIT;');
    }

    public function Rollback()
    {
        return $this->Connector()->exec('ROLLBACK;');
    }




}
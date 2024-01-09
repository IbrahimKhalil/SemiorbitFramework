<?php


namespace Semiorbit\Debug;


use Semiorbit\Base\Application;

class FileLog
{

    const DEFAULT_LOG_DIR = 'var/log/';

    const DEV_LOG = 'dev_log';


    const INFO = 'INFO';

    const DEBUG = 'DEBUG';

    const WARNING = 'WARNING';

    const ERROR = 'ERROR';


    protected $_LogID;

    protected $_Stats = [self::INFO => 0, self::WARNING => 0, self::ERROR => 0, self::DEBUG => 0];

    protected static $_ActiveLogger;

    protected static array $_MemLogs = [];


    public function __construct($log_id = null, $append = false)
    {

        $this->_LogID = $log_id ?: uniqid('', true);

        if (! $append && file_exists($this->Path()))

            unlink($this->Path());

    }

    public static function Start($log_id = null, $append = false)
    {

        $log = new static($log_id, $append);

        static::$_MemLogs[$log_id] = $log;

        static::$_ActiveLogger = static::$_MemLogs[$log_id];

        if (! $append) $log->Log(static::INFO, '', date("Y-m-d H:i:s"), '--- Logging Started ---');

        return $log;

    }

    public static function UseLog($log_id, $append = true)
    {
        return static::$_MemLogs[$log_id] ?? static::Start($log_id, $append);
    }

    public static function UseDevLog()
    {
        return static::UseLog(self::DEV_LOG);
    }

    public static function End(): void
    {
        static::ActiveLogger()->LogStats();

        unset(static::$_MemLogs[static::ActiveLogger()->_LogID]);
    }

    public static function ActiveLogger()
    {
        return static::$_ActiveLogger ?: static::$_ActiveLogger = static::Start();
    }

    public static function LogDirPath()
    {

        if (! file_exists($dp = Application::BasePath() . static::DEFAULT_LOG_DIR))

            mkdir($dp, 0777, true);

        return $dp;

    }

    public function Stats()
    {
        return $this->_Stats;
    }

    public function Path()
    {
        return static::LogDirPath() . "{$this->_LogID}.log";
    }

    public function Log($status, $code, $at, $msg)
    {

        $this->_Stats[$status]++;

        return file_put_contents(

            $this->Path(),

            date("Y-m-d H:i:s") . " {$status} {$code} --- [{$at}] {$msg}" . PHP_EOL,

            FILE_APPEND

        );

    }

    public function LogStats()
    {
        return $this->Log(static::INFO, '', 'END', ' --- ' . json_encode($this->_Stats));
    }



    public static function Info($code, $at, $msg)
    {
        return static::ActiveLogger()->Log(static::INFO, $code, $at, $msg);
    }

    public static function Debug($code, $at, $msg)
    {
        return static::ActiveLogger()->Log(static::DEBUG, $code, $at, $msg);
    }


    public static function Warning($code, $at, $msg)
    {
        return static::ActiveLogger()->Log(static::WARNING, $code, $at, $msg);
    }

    public static function Error($code, $at, $msg)
    {
        return static::ActiveLogger()->Log(static::ERROR, $code, $at, $msg);
    }

    /**
     * @return string
     */
    public function LogID()
    {
        return $this->_LogID;
    }

}
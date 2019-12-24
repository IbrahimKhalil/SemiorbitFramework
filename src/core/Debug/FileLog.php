<?php


namespace Semiorbit\Debug;


use Semiorbit\Base\Application;

class FileLog
{

    const DEFAULT_LOG_DIR = 'var/log/';


    const INFO = 'INFO';

    const DEBUG = 'DEBUG';

    const WARNING = 'WARNING';

    const ERROR = 'ERROR';


    protected $_LogID;

    protected $_Stats = [self::INFO => 0, self::WARNING => 0, self::ERROR => 0, self::DEBUG => 0];

    protected static $_ActiveLogger;



    public function __construct($log_id = null, $append = false)
    {
        $this->_LogID = $log_id ?: uniqid(null, true);

        if (! $append && file_exists($this->Path()))

            unlink($this->Path());

        $this->Log(static::INFO, '', 'START', '--- Logging Started ---');
    }

    public static function Start($log_id = null, $append = false)
    {
        return new static($log_id, $append);
    }

    public static function End()
    {
        return static::ActiveLogger()->LogStats();
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
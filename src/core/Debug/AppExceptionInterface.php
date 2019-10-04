<?php


namespace Semiorbit\Debug;


use Throwable;

interface AppExceptionInterface
{



    /**
     * AppException
     *
     * @param int $code Error code. A unique reference id for error message. <br>
     *                  This will be visible in both production and debug mode.
     * @param string $message Exception message will be visible to users in production mode
     *                          <br>If left empty, the message will be "An error occurred!" by default.
     * @param string $debug_msg Exception detailed message that will be visible only in "Debug Mode"
     * @param int $http_status_code Http code sent in response header.
     *                              <br>default code: 503
     * @param Throwable|null $previous
     */

    public function __construct($code, $message = "", $debug_msg = "",  $http_status_code = 503, Throwable $previous = null);

    /**
     * Get exception detailed message that will be visible only in "Debug Mode"
     *
     * @return mixed
     */

    public function getDebugMsg();

    /**
     * Set exception detailed message that will be visible only in "Debug Mode"
     *
     * @param mixed $debug_msg
     * @return static
     */

    public function setDebugMsg($debug_msg);

    /**
     * Http code sent in response header.
     *
     * @return mixed
     */

    public function getHttpStatusCode();


    /**
     * Http code sent in response header. <br>default code: 503
     *
     * @param mixed $HttpStatusCode
     * @return static
     */

    public function setHttpStatusCode($HttpStatusCode);


}
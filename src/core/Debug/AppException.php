<?php
/*
*-----------------------------------------------------------------------------------------------
* SEMIORBIT Exception       						    	  						    semiorbit.com
*-----------------------------------------------------------------------------------------------
*
*/

namespace Semiorbit\Debug;




use Throwable;
use Exception;

class AppException extends Exception implements AppExceptionInterface
{


    use AppExceptionTrait;

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

    public function __construct($code, $message = "", $debug_msg = "",  $http_status_code = 503, Throwable $previous = null)
    {

        parent::__construct($message, $code, $previous);

        $this->setHttpStatusCode($http_status_code)->setDebugMsg($debug_msg);

    }

}
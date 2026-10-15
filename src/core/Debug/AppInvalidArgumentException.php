<?php

namespace Semiorbit\Debug;

use InvalidArgumentException;
use Throwable;



/**
 * Invalid argument exception with localized messages, application error codes
 * and HTTP status metadata.
 */

class AppInvalidArgumentException extends InvalidArgumentException implements AppExceptionInterface
{

    use AppExceptionTrait;



    /**
     * Create an exception using the framework's standard exception contract.
     *
     * @param int $code Application error code.
     * @param string $message Plain English message or JSON language map.
     * @param string $debug_msg Details visible only in debug mode.
     * @param int $http_status_code HTTP response status.
     * @param Throwable|null $previous Original exception or error.
     */

    public function __construct(

        $code,

        $message = "",

        $debug_msg = "",

        $http_status_code = 503,

        ?Throwable $previous = null

    ) {

        parent::__construct($this->setErrorMessage($message)->English(), $code, $previous);

        $this->setHttpStatusCode($http_status_code)->setDebugMsg($debug_msg);

    }

}
<?php


namespace Semiorbit\Debug;


use Throwable;

trait AppExceptionTrait
{

    protected $_DebugMsg;

    protected $_HttpStatusCode;



    /**
     * Get exception detailed message that will be visible only in "Debug Mode"
     *
     * @return mixed
     */

    public function getDebugMsg()
    {
        return $this->_DebugMsg;
    }

    /**
     * Set exception detailed message that will be visible only in "Debug Mode"
     *
     * @param mixed $debug_msg
     * @return static
     */

    public function setDebugMsg($debug_msg)
    {
        $this->_DebugMsg = $debug_msg;

        return $this;
    }


    /**
     * Http code sent in response header.
     *
     * @return mixed
     */

    public function getHttpStatusCode()
    {
        return $this->_HttpStatusCode;
    }


    /**
     * Http code sent in response header. <br>default code: 503
     *
     * @param mixed $HttpStatusCode
     * @return static
     */

    public function setHttpStatusCode($HttpStatusCode)
    {
        $this->_HttpStatusCode = $HttpStatusCode;

        return $this;
    }

}
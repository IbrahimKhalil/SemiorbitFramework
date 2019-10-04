<?php
/*
 *------------------------------------------------------------------------------------------------
 * SEMIORBIT - RESPONSE HEADERS                                               semiorbit.com
 *------------------------------------------------------------------------------------------------
 *
 * Some functions are derived from Symfony Response Class in Symfony\Component\HttpFoundation
 *
 */

namespace Semiorbit\Http;


class ResponseHeaders implements ResponseStatus
{

    protected $_Headers = array();

    protected $_Protocol = 'HTTP/1.1';

    protected $_StatusCode;

    protected $_StatusMessage;


    public function Headers()
    {
        return $this->_Headers;
    }

    public function SendHeaders()
    {

        $this->SendStatus();

        if (! headers_sent())

            foreach ($this->_Headers as $key => $value) header("{$key}: $value");

    }

    public function SendStatus()
    {
        if (! headers_sent())

            header($this->Protocol() . ' ' . $this->StatusCode() . ' ' . $this->StatusMessage());
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */

    public function setHeader($key, $value)
    {
        $this->_Headers[$key] = $value;

        return $this;
    }

    public function removeHeader($key)
    {
        unset($this->_Headers[$key]);

        return $this;
    }

    public function Header($key)
    {
        return isset($this->_Headers[$key]) ? $this->_Headers[$key] : null;
    }

    public function setContentType($value, $charset = 'UTF-8')
    {
        $this->setHeader('Content-Type', $value . '; charset=' . $charset);

        return $this;
    }

    public function setStatus($code, $message = null)
    {

        $this->_StatusCode = intval($code);

        $this->IsInvalid() ? $this->_StatusCode = null :

            $this->_StatusMessage = $message ?: $this->DefaultStatusMessage($code);

        return $this;

    }

    public function setProtocol($protocol)
    {
        $this->_Protocol = $protocol ?: 'HTTP/1.1';

        return $this;
    }

    public function Protocol()
    {
        return $this->_Protocol;
    }

    public function DefaultStatusMessage($code)
    {
        return static::STATUS_MESSAGES[$code] ?? null;
    }
    
    public function StatusCode()
    {
        return $this->_StatusCode ?: $this->_StatusCode = static::HTTP_OK;
    }
    
    public function StatusMessage()
    {
        return $this->_StatusMessage ?: static::DefaultStatusMessage($this->StatusCode());
    }


    /**
     * Is response invalid?
     *
     * @return bool
     *
     * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
     */
    public function IsInvalid()
    {
        return $this->_StatusCode < 100 || $this->_StatusCode >= 600;
    }

    /**
     * Is response informative?
     *
     * @return bool
     */
    public function IsInformational()
    {
        return $this->_StatusCode >= 100 && $this->_StatusCode < 200;
    }

    /**
     * Is response successful?
     *
     * @return bool
     */
    public function IsSuccessful()
    {
        return $this->_StatusCode >= 200 && $this->_StatusCode < 300;
    }

    /**
     * Is the response a redirect?
     *
     * @return bool
     */
    public function IsRedirection()
    {
        return $this->_StatusCode >= 300 && $this->_StatusCode < 400;
    }

    /**
     * Is there a client error?
     *
     * @return bool
     */
    public function IsClientError()
    {
        return $this->_StatusCode >= 400 && $this->_StatusCode < 500;
    }

    /**
     * Was there a server side error?
     *
     * @return bool
     */
    public function IsServerError()
    {
        return $this->_StatusCode >= 500 && $this->_StatusCode < 600;
    }

    /**
     * Is the response OK?
     *
     * @return bool
     */
    public function IsOk()
    {
        return 200 === $this->_StatusCode;
    }

    /**
     * Is the response forbidden?
     *
     * @return bool
     */
    public function IsForbidden()
    {
        return 403 === $this->_StatusCode;
    }

    /**
     * Is the response a not found error?
     *
     * @return bool
     */
    public function IsNotFound()
    {
        return 404 === $this->_StatusCode;
    }

    /**
     * Is the response a redirect of some form?
     *
     *
     * @return bool
     */
    public function IsRedirect()
    {
        return in_array($this->_StatusCode, array(201, 301, 302, 303, 307, 308));
    }

    /**
     * Is the response empty?
     *
     * @return bool
     */
    public function IsEmpty()
    {
        return in_array($this->_StatusCode, array(204, 304));
    }

    public function HasStatusCode($code)
    {
        return array_key_exists($code, static::STATUS_MESSAGES);
    }

}
<?php
/*
 *------------------------------------------------------------------------------------------------
 * SEMIORBIT - RESPONSE                                                       semiorbit.com
 *------------------------------------------------------------------------------------------------
 */


namespace Semiorbit\Http;


use Semiorbit\Config\CFG;
use Semiorbit\Debug\AppException;
use Semiorbit\Output\View;
use Semiorbit\Output\ViewBase;

class Response extends ResponseHeaders
{

    protected $_Content;

    protected $_View;


    public function Json($data, $options = JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT)
    {

        $this->setContentType('application/json');

        $this->_Content = json_encode($data, $options);

        return $this;

    }


    protected function ErrorHandler($code, $msg = '', $debug_msg = '', $http_status_code = 0, \Exception $e = null)
    {

        if (! $http_status_code)

            $http_status_code = $this->HasStatusCode($code) ? $code : 503;


        $content = [

            'status' => 'error',

            'code' => $code,

            'msg' => $msg ?: $this->DefaultStatusMessage($http_status_code),

            'http_status_code' => $http_status_code

        ];


        if (CFG::$DebugMode) {

            $content = array_merge($content, [

                'debug_msg' => $debug_msg,

                'file' => $e->getFile() ?? '',

                'line' => $e->getLine() ?? '',

                'trace' => (CFG::$ApiMode ? $e->getTraceAsString() : $e->getTrace()) ?? []

            ]);

        }

        // Set http status

        $this->setStatus($http_status_code, $content['msg']);


        // Set response content

        if (CFG::$ApiMode) {

            $this->Json($content);

        } else {

            $this->setContent(

                View::Load(['errors/' . $content['code'], 'errors/default'])->WithParams($content)

            );

        }

        return $this;

    }


    public function Exception(\Exception $e)
    {

        if ($e instanceof AppException) {

            $this->ErrorHandler($e->getCode(), $e->getMessage(), $e->getDebugMsg(), $e->getHttpStatusCode(), $e);

        } else {

            $this->ErrorHandler($e->getCode(), '', $e->getMessage(), 0, $e);

        }
        die($this->Send(false));

        return $this;

    }


    public static function SendException(\Exception $e, $flush_output = true)
    {

        $response = new static;

        return $response->Exception($e)->Send($flush_output);

    }


    public function UseView(ViewBase $view)
    {

        $this->setContentType('text/html');

        $this->_View = $view;

        return $this;

    }

    /**
     * @return ViewBase
     */

    public function ActiveView()
    {
        return $this->_View;
    }



    public function setContent($data)
    {
        $this->_Content = $data;

        return $this;
    }

    public function Content()
    {

        if ($this->_View)

            return $this->ActiveView()->Render(false);

        else return $this->_Content;

    }

    /**
     * @param bool $flush_output
     * @return string
     */

    public function Send($flush_output = true)
    {

        $this->SendHeaders();

        if ($flush_output) echo $this->Content();

        return $this->_Content;

    }

    public function __toString()
    {
        return strval( $this->Send(false) );
    }

}
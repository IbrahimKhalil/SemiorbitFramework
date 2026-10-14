<?php
/*
 *------------------------------------------------------------------------------------------------
 * SEMIORBIT - RESPONSE                                                       semiorbit.com
 *------------------------------------------------------------------------------------------------
 */


namespace Semiorbit\Http;


use Semiorbit\Base\AppManager;
use Semiorbit\Config\Config;
use Semiorbit\Debug\AppExceptionInterface;
use Semiorbit\Debug\ErrorMessage;
use Semiorbit\Output\View;
use Semiorbit\Output\ViewBase;

class Response extends ResponseHeaders
{

    protected $_Content;

    protected $_View;


    public function Json($data, $options = JSON_UNESCAPED_UNICODE ): static
    {

        $this->setContentType('application/json');

        $this->_Content = json_encode($data, $options);

        return $this;

    }



    protected function ErrorHandler($code, $msg = '', $debug_msg = '',  $http_status_code = 0, ?\Exception $e = null): static
    {

        if (!$http_status_code)

            $http_status_code = $this->HasStatusCode($code) ? (int) $code : 503;


        $message = ErrorMessage::Read((string) ($msg ?: $this->DefaultStatusMessage($http_status_code)));


        $content = [

            'status' => $http_status_code,

            'code' => (int) $code ?: $http_status_code,

            'msg' => $message->Raw(),

            'type' => $this->ErrorType($code ?: $http_status_code)

        ];



        if (Config::DebugMode()) {

            $content = array_merge($content, [

                'debug_msg' => $debug_msg,

                'file' => $e?->getFile() ?? '',

                'line' => $e?->getLine() ?? '',

                'trace' => (Config::ApiMode())

                    ? ($e?->getTraceAsString() ?? '')

                    : ($e?->getTrace() ?? [])

            ]);

        }


        $this->setStatus($http_status_code);


        if (Config::ApiMode()) {

            $content['msg'] = ($message->IsTranslated()) ? $message->ToArray() : $message->English();

            $this->Json($content);

        } else {

            $this->setContent(

                View::Load(['errors/' . $content['status'], 'errors/default'])

                    ->NoLayout()

                    ->WithParams($content)

            );

        }


        return $this;

    }



    public function Exception(\Exception $e): static
    {

        if ($e instanceof AppExceptionInterface) {


            $message = (method_exists($e, 'getErrorMessage'))

                ? $e->getErrorMessage()->Raw()

                : $e->getMessage();


            $this->ErrorHandler($e->getCode(), $message, $e->getDebugMsg(), $e->getHttpStatusCode(), $e);

        } else {

            $this->ErrorHandler($e->getCode(), '', $e->getMessage(), 0, $e);

        }

        return $this;

    }



    public static function SendException(\Exception $e, $flush_output = true)
    {

        $response = new static;

        return $response->Exception($e)->Send($flush_output);

    }



    /**
     * Build the error type from the host, application metadata and error code.
     *
     * @param int $code
     * @return string
     */

    protected function ErrorType($code): string
    {

        $app = AppManager::MainApp();

        $application = $app->ApplicationClass();

        $parts = [(string) $app->Composer('name')];

        foreach (['Id', 'DbCode'] as $method) {

            if (! method_exists($application, $method)) continue;

            $value = (string) $application::$method();

            if ($value !== '') $parts[] = $value;

        }

        $parts[] = (string) $code;


        return 'tag:' . rtrim(Url::Host(), '/') . ',' . $application::LicenseYear()

            . ':' . implode('/', $parts);

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

    public function Send(bool $flush_output = true)
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
<?php
namespace Semiorbit\Controllers;


use Semiorbit\Http\ResponseStatus;
use Semiorbit\Http\RestController;

class HttpErrorController extends RestController
{

    public function Index()
    {

        $error_code = ResponseStatus::HTTP_NOT_FOUND;


        $content = [

            'status' => 'error',

            'code' => $error_code,

            'msg' => $this->Response->DefaultStatusMessage($error_code)

        ];


        $this->Response

            ->setStatus(ResponseStatus::HTTP_NOT_FOUND)

            ->Json($content)

            ->Send();

    }

}
<?php
/*
*------------------------------------------------------------------------------------------------
* MSG - SEMIORBIT MESSAGE BAG & MANAGER   			 				 semiorbit.com
*------------------------------------------------------------------------------------------------
*
*/

namespace Semiorbit\Data;



use Semiorbit\Output\Render;
use Semiorbit\Output\Widget;
use Semiorbit\Translation\Lang;


class Msg
{
    const DBERR             =   0;

    const DBOK              =   1;

    const FILL_ALL          =   2;

    const FILL_ALL_REQUIRED =   3;

    const DATA_EXISTS       =   4;

    const INVALID_TEL       =   5;

    const INVALID_VALUE     =   6;

    const INVALID_URL       =   7;

    const INVALID_EMAIL     =   8;

    const RETYPE_PASSWORD   =   9;

    const ROW_DELETED       =   10;

    const REGISTER_OK       =   11;

    const REGISTER_CANCEL   =   12;

    const REGISTER_ACTIVE   =   13;

    const REGISTER_FAILED   =   14;

    const UPLOAD_OK         =   15;

    const UPLOAD_FAILED     =   16;

    const FILE_TYPE_ERR     =   17;

    const RESIZE_FAILED     =   18;

    const FILE_SIZE_ERR     =   19;

    const NOT_VERIFIED      =   20;

    const COMMENTED         =   21;


    /**
     * @param $err_str_handle
     * @param int $type
     * @return Widget
     */

    public static function Show($err_str_handle, $type = 0)
    {

        $pms['err_str_handle'] = $err_str_handle;

        $pms['msg'] = is_string( $err_str_handle ) ? $err_str_handle : static::Text($err_str_handle);

        $pms['type'] = $type;

        return Render::Widget('msg')->WithParams($pms);

    }

    public static function Text($msg)
    {
        return Lang::Trans( "semiorbit::msg." . $msg );
    }

}

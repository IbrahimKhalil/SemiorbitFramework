<?php
/*
 *------------------------------------------------------------------------------------------------
 * SEMIORBIT - CLIENT INFORMATION 				 					 semiorbit.com
 *------------------------------------------------------------------------------------------------
 */

namespace Semiorbit\Http;


class Client
{

    public static $MobileDomain = '';

    private static $_IsMobile = null;

    private static $_IsMobileDomain = null;

    public static function IsMobile($check_mobile_domain = true)
    {
        $is_mobile = (bool)preg_match('#\b(ip(hone|od)|android\b.+\bmobile|opera m(ob|in)i|windows (phone|ce)|blackberry'.
            '|s(ymbian|eries60|amsung)|p(alm|rofile/midp|laystation portable)|nokia|fennec|htc[\-_]'.
            '|up\.browser|[1-4][0-9]{2}x[1-4][0-9]{2})\b#i', $_SERVER['HTTP_USER_AGENT'] );

        if ( $check_mobile_domain ) return static::$_IsMobile = static::IsMobileDomain() ?: $is_mobile;

        return static::$_IsMobile = $is_mobile;

    }

    public static function IsMobileDomain()
    {
        if ( static::$_IsMobileDomain !== null ) return static::$_IsMobileDomain;

        if ( is_empty( static::$MobileDomain ) ) return static::$_IsMobileDomain = false;

        $host = $_SERVER['HTTP_HOST'];

        $host = str_ireplace("http://", "", $host);

        $host = str_ireplace("https://", "", $host);

        $host = trim($host, "/");

        return static::$_IsMobileDomain = ( $host == static::$MobileDomain );

    }

    public static function MSIE_X_UA_CompatibleHeader()
    {
        if (isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false))	header('X-UA-Compatible: IE=edge,chrome=1');
    }

    public static function IP()
    {
        $ip = getenv('HTTP_CLIENT_IP') ?:
            getenv('HTTP_X_FORWARDED_FOR') ?:
                getenv('HTTP_X_FORWARDED') ?:
                    getenv('HTTP_FORWARDED_FOR') ?:
                        getenv('HTTP_FORWARDED') ?:
                            getenv('REMOTE_ADDR');

        if (stristr($ip, "::1")) $ip = "127.0.0.1";

        if (filter_var($ip, FILTER_VALIDATE_IP) === false) return false;

        return $ip;

    }

}
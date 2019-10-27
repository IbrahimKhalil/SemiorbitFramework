<?php
/*
 *------------------------------------------------------------------------------------------------
 * SEMIORBIT - URL MANAGER  						 					 semiorbit.com
 *------------------------------------------------------------------------------------------------
 */

namespace Semiorbit\Http;


use Semiorbit\Config\Config;
use Semiorbit\Output\Render;
use Semiorbit\Support\Path;


class Url
{
    public static $_BaseUrl;

    public static function UseBaseUrl($base_url)
    {
        static::$_BaseUrl = $base_url;
    }

    public static function BaseUrl($return_protocol = true, $return_host = true, $return_dir = true)
    {

        if ($return_dir && $return_host && $return_protocol && static::$_BaseUrl) return static::$_BaseUrl;

        return static::FindBaseUrl($return_protocol, $return_host, $return_dir);

    }

    public static function FindBaseUrl($return_protocol = true, $return_host = true, $return_dir = true)
    {

        /* First we need to get the protocol the website is using */
        $protocol = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://');


        /* returns /myproject/index.php */
        $path = Config::IndexFilePath() != '' ? Config::IndexFilePath() : $_SERVER['SCRIPT_NAME'];

        if ( $path ) {

            /*
             * returns an array with:
            * Array (
                    *  [dirname] => /myproject/
                    *  [basename] => index.php
                    *  [extension] => php
                    *  [filename] => index
                    * )
            */
            $path_parts = pathinfo($path);

            $directory = $path_parts['dirname'];

        } else {

            $directory = SymfonyRequest::Load()->getBasePath();

        }

        /*
         * If we are visiting a page off the base URL, the dirname would just be a "/",
        * If it is, we would want to remove this
        */
        $directory = ($directory == "/") ? "" : $directory;

        $directory = rtrim($directory, "/") . "/";

        /* Returns localhost OR mysite.com */
        $host = $_SERVER['HTTP_HOST'] ?? '';

        /*
         * Returns:
        * http://localhost/mysite
        * OR
        * https://mysite.com
        */

        $url = "";
        if ($return_protocol) $url .= $protocol;
        if ($return_host) $url .= $host;
        if ($return_dir) $url .= $directory;

        return Path::Normalize($url);
    }
    
    
    public static function Host()
    {
        return Url::BaseUrl(false, true, false);
    }
    
    public static function BaseDir()
    {
        return Url::BaseUrl(false, false, true);
    }


    /**
     * Does a UTF-8 safe version of PHP parse_url function
     *
     * @param   string $url URL to parse
     *
     * @return  mixed  Associative array or false if badly formed URL.
     *
     * @see     http://us3.php.net/manual/en/function.parse-url.php
     * @since   11.1
     */
    public static function Utf8ParseUrl($url)
    {
        $result = false;

        // Build arrays of values we need to decode before parsing

        $entities = array('%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%24', '%2C', '%2F', '%3F', '%23', '%5B', '%5D');

        $replacements = array('!', '*', "'", "(", ")", ";", ":", "@", "&", "=", "$", ",", "/", "?", "#", "[", "]");

        // Create encoded URL with special URL characters decoded so it can be parsed

        // All other characters will be encoded

        $encoded_url = str_replace($entities, $replacements, urlencode($url));

        // Parse the encoded URL

        $encoded_parts = parse_url($encoded_url);

        // Now, decode each value of the resulting array

        if ($encoded_parts) {
            foreach ($encoded_parts as $key => $value) {
                $result[$key] = urldecode(str_replace($replacements, $entities, $value));
            }
        }

        return $result;
    }


    public static function HomePage()
    {

        $main_controller = Config::MainPage();

        $home_page = Controller::Name($main_controller['class']);

        return $home_page;

    }


    public static function setPreviousPage($previous_page_url = null)
    {
        $purl = $previous_page_url ?: Request::Url();

        if (stristr($purl, "login") || stristr($purl, "logout") || stristr($purl, "/load") || stristr($purl, "/run")

        ) {

            $_SESSION['semiorbit_previous_page'] = BASE_URL . LANG;

        } else {

            $_SESSION['semiorbit_previous_page'] = $purl;

        }

    }

    public static function CurrentPage(array $set_params = null, array $exclude_params = array())
    {

        $url = Request::Url();

        if ( ! empty($set_params) ) {

            $url = substr( $url, 0, strpos( $url, '?' ) ) . '?' . Url::Params( $exclude_params, $set_params );

        }

        return $url;

    }

    public static function PreviousPage()
    {

        if (empty($_SESSION['semiorbit_previous_page'])) {

            $_SESSION['semiorbit_previous_page'] = BASE_URL . LANG;

        }

        return $_SESSION['semiorbit_previous_page'] = str_ireplace(Config::LayoutParamName() . "=none", "",

            $_SESSION['semiorbit_previous_page']);

    }

    public static function GotoPreviousPage($query_string = '')
    {

        $pms['location'] = Url::ConcatUrl(str_replace($query_string, '', Url::PreviousPage()), $query_string);

        Render::Widget('window-location', $pms)->Render();

    }

    public static function GotoHomePage($query_string = '')
    {

        $pms['location'] = Url::ConcatUrl(BASE_URL . LANG . '/', $query_string);

        Render::Widget('window-location', $pms)->Render();

    }

    public static function GotoPage($url = '')
    {

        $pms['location'] = $url;

        Render::Widget('window-location', $pms)->Render();

    }


    public static function ConcatUrl($url, $query_string)
    {

        if (stristr($url, '?')) {

            $full_url = trim($url, '&') . '&' . trim($query_string, '&');

        } else {

            $full_url = $url . (!is_empty($query_string) ? '?' . trim($query_string, '&') : '');

        }

        return $full_url;

    }

    public static function Params(array $exclude_params = array(), array $set_params = array())
    {

        $pms = array();

        foreach ( $_GET as $pm_key => $pm_val ) {

            // Avoid adding url path, in some cases when url path
            // is added to query string in .htaccess file for example.

            if (starts_with($pm_key, '/')) continue;


            if ( ! in_array( $pm_key, $exclude_params ) ) {

                $pms[] = $pm_key . "=" . urlencode( isset( $set_params[ $pm_key ] ) ? $set_params[ $pm_key ] : $pm_val );

            }

        }

        foreach( $set_params as $pm_key => $pm_val ) {

            if ( ! isset( $_GET[ $pm_key ] ) )

                $pms[] = $pm_key . "=" . urlencode( $pm_val );

        }

        $str_pms = @join("&", $pms);

        return ($str_pms);

    }

    public static function QueryString(array $exclude_params = array(), array $set_params = array(), $prepend = '?')
    {

        $params = static::Params($exclude_params, $set_params);

        return ! empty($params) ? $prepend . $params : '';

    }


    public static function DecodeUnicodeUrl($str)
    {
        $res = '';

        $i = 0;

        $max = strlen($str) - 6;

        while ($i <= $max) {

            $character = $str[$i];

            if ($character == '%' && $str[$i + 1] == 'u') {

                $value = hexdec(substr($str, $i + 2, 4));

                $i += 6;

                if ($value < 0x0080) // 1 byte: 0xxxxxxx
                {
                    $character = chr($value);
                } elseif ($value < 0x0800) // 2 bytes: 110xxxxx 10xxxxxx
                {
                    $character =
                        chr((($value & 0x07c0) >> 6) | 0xc0)
                        . chr(($value & 0x3f) | 0x80);
                } else // 3 bytes: 1110xxxx 10xxxxxx 10xxxxxx
                {
                    $character =
                        chr((($value & 0xf000) >> 12) | 0xe0)
                        . chr((($value & 0x0fc0) >> 6) | 0x80)
                        . chr(($value & 0x3f) | 0x80);
                }
            } else
                $i++;

            $res .= $character;
        }

        return $res . substr($str, $i);

    }

    public static function UrlToDomain($url, $add_www = true)
    {

        if (trim($url) == "") return '';

        $url = str_ireplace( "http://", "", $url );

        $url = str_ireplace( "https://", "", $url );

        $url = str_ireplace( "www.", "", $url );

        $url = trim( $url, "/" );

        if ($add_www) $url = "www." . $url;

        return $url;

    }


    public static function DomainToUrl($domain)
    {

        if ( trim($domain) == "" ) return '';

        if ( starts_with( $domain, "https://" ) ) return $domain;

        if ( starts_with( $domain, "http://" ) ) return $domain;

        $url = "http://" . $domain;

        return $url;

    }

    public static function FileType($url)
    {
        return strtolower( pathinfo( parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) );
    }



}
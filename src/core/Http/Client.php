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

    private static $_PreferredLanguages = [];

    private static $_Locale;

    private static $_Language;

    private static $_AcceptLanguage;

    private static $_UserAgent;

    private static $_Ip;



    public static function IsMobile($check_mobile_domain = true)
    {

        if (static::$_IsMobile) return static::$_IsMobile;

        $is_mobile = (bool) preg_match('#\b(ip(hone|od)|android\b.+\bmobile|opera m(ob|in)i|windows (phone|ce)|blackberry'.
            '|s(ymbian|eries60|amsung)|p(alm|rofile/midp|laystation portable)|nokia|fennec|htc[\-_]'.
            '|up\.browser|[1-4][0-9]{2}x[1-4][0-9]{2})\b#i', static::UserAgent() );

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

        if (isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false))

            header('X-UA-Compatible: IE=edge,chrome=1');

    }


    public static function IP()
    {

        if (static::$_Ip) return static::$_Ip;

        $ip = getenv('HTTP_CLIENT_IP') ?:

            getenv('HTTP_X_FORWARDED_FOR') ?:

                getenv('HTTP_X_FORWARDED') ?:

                    getenv('HTTP_FORWARDED_FOR') ?:

                        getenv('HTTP_FORWARDED') ?:

                            getenv('REMOTE_ADDR');

        if (stristr($ip, "::1")) $ip = "127.0.0.1";

        if (filter_var($ip, FILTER_VALIDATE_IP) === false) return false;

        return static::$_Ip = $ip;

    }


    public static function Language()
    {
        return static::$_Language ?:

            static::$_Language = substr(static::AcceptLanguage(), 0, 2);
    }


    public static function Locale()
    {

        if (static::$_Locale) return static::$_Locale;

        $lang_list = static::ListLanguages();

        foreach ($lang_list as $locale => $rank) {

            if (strlen($locale) >= 5)

                return static::$_Locale = $locale;

        }

        return static::$_Locale =  array_shift($lang_list);

    }


    public static function AcceptLanguage()
    {
        return static::$_AcceptLanguage ?:

            static::$_AcceptLanguage = getenv('HTTP_ACCEPT_LANGUAGE');
    }


    public static function UserAgent()
    {
        return static::$_UserAgent ?:

            static::$_UserAgent = getenv('HTTP_USER_AGENT');
    }


    /**
     * List languages and locales from http accept language with ranks
     * [locale => rank, ...]
     *
     * @param string|null $accepted_languages
     * @return array
     *
     * @link https://stackoverflow.com/questions/6038236/using-the-php-http-accept-language-server-variable
     */

    public static function ListLanguages (string $accepted_languages = null)
    {

        if (!$accepted_languages)

            $accepted_languages = static::AcceptLanguage();



        if (!$accepted_languages && static::$_PreferredLanguages) {

            $pref_lang = static::$_PreferredLanguages;

        } else {


            // regex inspired from @GabrielAnderson on http://stackoverflow.com/questions/6038236/http-accept-language

            preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})*)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $accepted_languages, $lang_parse);

            $languages = $lang_parse[1];

            $ranks = $lang_parse[4];


            // Create an associative array 'language' => 'preference'

            $pref_lang = [];

            for ($i = 0; $i < count($languages); $i++)

                $pref_lang[$languages[$i]] = (float) ($ranks[$i] ?: 1);

        }


        reset($pref_lang);

        return $accepted_languages ? $pref_lang : (static::$_PreferredLanguages = $pref_lang);

    }



}
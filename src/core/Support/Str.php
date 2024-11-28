<?php
/*
*------------------------------------------------------------------------------------------------
* SEMIORBIT - STRING HELPER    					 					 semiorbit.com
*------------------------------------------------------------------------------------------------
*/

namespace Semiorbit\Support;


use Semiorbit\Config\Config;



class Str
{

    use ClipboardTrait {
        Clipboard as protected;
    }

    /**
     * Convert a string to snake_case.
     *
     * @param $str
     * @param string $delimiter
     * @return string
     */

    public static function SnakeCase($str, $delimiter = "_")
    {
        // Modified starting from Laravel source code of str::snake() function.

        $str = $str === null ? '' : $str;

        $key = $str . $delimiter;

        if (!is_empty(self::Clipboard($key))) {

            return self::Clipboard($key);

        }

        if (!ctype_lower($str) && !ctype_upper($str)) {

            $str = preg_replace('/(.)(?=[A-Z])/', '$1' . $delimiter, $str);

        }

        $str = strtolower($str);

        return self::Clipboard($key, $str);

    }

    /**
     * Convert a string to param-case.
     *
     * @param $str
     * @param string $delimiter
     * @return string
     */

    public static function ParamCase($str, $delimiter = "-")
    {
        return static::SnakeCase($str, $delimiter);
    }

    /**
     * Convert a value to PascalCase.
     *
     * @param  string $str
     * @param  string|array $delimiter
     * @return string
     */

    public static function PascalCase($str, $delimiter = ['-', '_'])
    {

        $key = 'P:' . $str;

        if (!is_empty(self::Clipboard($key))) return self::Clipboard($key);

        $str = str_replace(' ', '', ucwords(str_replace($delimiter, ' ', $str)));

        return self::Clipboard($key, $str);


    }


    /**
     * Convert a value to PascalCase using <b>only</b> dash (hyphen) as delimiter.
     *
     * @param  string $str
     * @param  string|array $delimiter
     * @return string
     */

    public static function PascalCaseByHyphen($str, $delimiter = '-')
    {
        return static::PascalCase($str, $delimiter);
    }

    /**
     * Convert a value to camelCase.
     *
     * @param  string $str
     * @param  string|array $delimiter
     * @return string
     */

    public static function CamelCase($str, $delimiter = ['-', '_'])
    {

        $key = 'C:' . $str;

        if (!is_empty(self::Clipboard($key))) return self::Clipboard($key);

        $str = lcfirst(static::PascalCase($str, $delimiter));

        return self::Clipboard($key, $str);

    }


    /**
     * Convert a value to camelCase using <b>only</b> dash (hyphen) as delimiter.
     *
     * @param  string $str
     * @param  string|array $delimiter
     * @return string
     */

    public static function CamelCaseByHyphen($str, $delimiter = '-')
    {
        return static::CamelCase($str, $delimiter);
    }


    /**
     * Convert a value to PascalCase_en and keep lang suffix at the end as is.
     *
     * @param  string $str
     * @return string
     */

    public static function PascalCaseKeepLang($str)
    {

        $key = 'PL:' . $str;

        if (!is_empty(self::Clipboard($key))) return self::Clipboard($key);

        $keep_lang = false;

        $lang = current(array_reverse(explode("_", $str)));

        if (in_array($lang, array_merge(Config::Languages(), ['l1', 'l2', 'l3', 'l4']))) {

            $str = rtrim($str, "_" . $lang);

            $keep_lang = true;

        }

        $str = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $str)));

        return self::Clipboard($key, $keep_lang ? $str . "_" . $lang : $str);

    }

    /**
     * Convert the string to Title Case.
     *
     * @param  string $str String to convert
     * @return string
     */

    public static function Title($str)
    {
        return mb_convert_case($str, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Split PascalCase string by uppercase chars.
     * eg. PascalCase => Pascal Case
     *
     * @param string $str String to split
     * @param string $glue Separator
     * @return array|string
     */

    public static function SplitByCaps($str, $glue = "")
    {

        $re = '/# Match position between camelCase "words".
    (?<=[a-z])  # Position is after a lowercase,
    (?=[A-Z])   # and before an uppercase letter.
    /x';

        $a = preg_split($re, $str);

        if ($glue != "") $a = implode($glue, $a);

        return $a;

    }


    /**
     * Strip tags and encode quotes
     *
     * @param string $input
     * @return string
     */

    public static function Filter(?string $input) : string
    {
        return str_replace(['\'', '"'], ['&#39;', '&#34;'], strip_tags($input));
    }


    /**
     * Remove or replace new line characters [CR/LF] in a string
     *
     * @param $subject
     * @param string $delimiter
     * @return string
     */

    public static function RemCrLf($subject, $delimiter = "")
    {

        $new_string = preg_replace("/[\n\r]/", $delimiter, $subject);

        return str_ireplace("{$delimiter}{$delimiter}", "{$delimiter}", $new_string);

    }

    public static function HashTags($string, $trim = ":.,;?* ")
    {

        preg_match_all('/#([^\s]+)/', $string, $matches);

        $hash = $matches[1];

        $tags = $hash;

        if (!empty($trim)) {

            $tags = array();

            foreach ($hash as $tag) $tags[] = trim($tag, $trim);

        }

        return $tags;

    }

    public static function Nl2Li($str, $css_class = "", $ordered = 0, $type = "1")
    {

        //check if its ordered or unordered list, set tag accordingly

        if ($ordered) {

            $tag = "ol";

            //specify the type
            $tag_type = "type=$type";

        } else {

            $tag = "ul";

            //set $type as NULL
            $tag_type = NULL;

        }

        // add ul / ol tag
        //// add tag type
        //// add first list item starting tag - use css class
        //// add last list item ending tag
        $str = "<$tag $tag_type class=\"$css_class\"><li>" . $str . "</li></$tag>";

        //replace /n with adding two tags
        // add previous list item ending tag
        // add next list item starting tag - use css class
        $str = str_replace("\n", "</li>\n<li>", $str);

        //spit back the modified string
        return $str;

    }

    public static function ArrayToList($array, $item_format = '', $css_class = '', $id = '', $ordered = false)
    {

        $attrs = $css_class ? "class=\"{$css_class}\"" : '';

        $attrs .= $id ? " id=\"{$id}\"" : '';

        $str = $ordered ? "<ol {$attrs}>" : "<ul {$attrs}>";

        foreach ($array as $key => $item) {

            if ($item_format) $item = sprintf($item_format, $key, $item);

            $str .= PHP_EOL . "<li>{$item}</li>" . PHP_EOL;

        }

        $str .= $ordered ? "</ol>" : "</ul>";

        return $str;

    }


    /**
     * Remove all characters that matches the pattern in a string
     *
     * @param string $input
     * @param string $pattern
     * @return string
     */
    public static function Sanitize($input, $pattern = '/[^a-zA-Z0-9_-]/')
    {
        return preg_replace($pattern, '', (string) $input);

    }


    /**
     * Get the first (N) of words from a sentece
     *
     * @param $sentence
     * @param int $count
     * @return mixed
     * @see https://stackoverflow.com/questions/5956610/how-to-select-first-10-words-of-a-sentence
     */

    public static function MaxWords($sentence, $count = 10)
    {
        preg_match("/(?:\w+(?:\W+|$)){0,$count}/u", $sentence, $matches);

        return $matches[0];
    }

    /**
     * @param int $length
     * @return string
     * @link http://stackoverflow.com/questions/6287903/how-to-properly-add-csrf-token-using-php
     */

    public static function GenerateRandomToken($length = 32)
    {

        return bin2hex(openssl_random_pseudo_bytes($length));

        // 7.0

        // bin2hex(random_bytes(32));

    }


    /**
     * Replace only the first and last occurrence
     *
     * @param $string
     * @param string $needle
     * @return string
     */

    public static function TrimOnce($string, $needle = ' ')
    {

        $string = self::LTrimOnce($string, $needle);

        $string = self::RTrimOnce($string, $needle);

        return $string;

    }

    /**
     * Replace only the first occurrence
     *
     * @param $string
     * @param string $needle
     * @return string
     */

    public static function LTrimOnce($string, $needle = ' ')
    {

        if (starts_with($string, $needle))

            $string = substr($string, strlen($needle));

        return $string;

    }

    /**
     * Replace only the last occurrence
     *
     * @param $string
     * @param string $needle
     * @return string
     */

    public static function RTrimOnce($string, $needle = ' ')
    {

        if (ends_with($string, $needle))

            $string = substr($string,0,strlen($string) - strlen($needle));

        return $string;

    }


    public static function ReplaceFirst($needle, $replace, $haystack)
    {
        return static::ReplaceOnce($needle, $replace, $haystack);
    }

    public static function ReplaceLast($needle, $replace, $haystack)
    {
        return static::ReplaceOnce($needle, $replace, $haystack, true);
    }

    protected static function ReplaceOnce($needle, $replace, $haystack, $from_right = false)
    {

        $pos = $from_right ? strripos($haystack, $needle) : stripos($haystack, $needle);

        if ($pos !== false)

            $haystack = substr_replace($haystack, $replace, $pos, strlen($needle));

        return $haystack;

    }


    public static function RemoveEmptyLines(?string $string)
    {
        return trim(preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $string), PHP_EOL);
    }


    /**
     * Add quotes around a string <b>'str'</b>
     *
     * @param string $str
     * @param string $quote Single quote by default 'str'
     * @return string
     */

    public static function Quote($str, string $quote = "'"): string
    {
        return $quote . $str . $quote;
    }


    /**
     * Add double quotes around a string <b>"str"</b>
     *
     * @param string $str
     * @return string
     */

    public static function DoubleQuote($str): string
    {
        return Str::Quote($str, '"');
    }

}
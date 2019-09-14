<?php
/*
*-----------------------------------------------------------------------------------------------
* SEMIORBIT - GLOBAL FUNCTIONS					 					 semiorbit.com
*------------------------------------------------------------------------------------------------
*/




use Semiorbit\Base\Application;
use Semiorbit\Base\AppManager;


function run($uri = '', $flush_output = true)
{
    return AppManager::MainApp()->Run($uri, $flush_output);
}

/**
 * abort
 *
 * prints custom err pages in he output when needed OR return
 * a string containing the custom err page.
 *
 *
 * @access   public
 * @param    int $code 404, 405, etc ...
 * @param string $message
 * @return string
 */

function abort($code, $message = '')
{
    Application::Abort($code, $message);
}

function watch($var_value, $trace = 1)
{
    \Semiorbit\Debug\Log::JsConsole()->Trace($trace)->TraceStartIndex(3)->Debug("", $var_value);
}

function dd($var_value, $trace = 0)
{
    \Semiorbit\Debug\Log::Inline()->Trace($trace)->TraceStartIndex(3)->Debug("", $var_value);
}

function msg($title, $msg)
{
    \Semiorbit\Debug\Log::Inline()->Trace(0)->Info($title, $msg);
}

function trans($key, $pms = [], $count = 0, $default = ':key')
{
    return \Semiorbit\Translation\Lang::Trans($key, $pms, $count, $default);
}

function project_title()
{
    return AppManager::CallMainApp('Title');
}

//DATABASE

function find($query, $params = [], $field = 0, $con = null)
{
    return \Semiorbit\Db\DB::Connection($con)->Find($query, $params, $field);
}

function cmd($query, $params = [], $con = '')
{
    return \Semiorbit\Db\DB::Connection($con)->Cmd($query, $params);
}

///STRING


function starts_with($haystack, $needle)
{
    return !strncmp($haystack, $needle, strlen($needle));
}

function ends_with($haystack, $needle)
{
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }

    return (substr($haystack, -$length) === $needle);
}

function str_replace_first($search, $replace, $subject)
{
    $pos = strpos($subject, $search);
    if ($pos !== false) {
        $subject = substr_replace($subject, $replace, $pos, strlen($search));
    }
    return $subject;
}

function is_empty(&$var)
{
    return is_string($var) ? trim($var) == '' : empty($var);
}

function nl2li($str, $css_class = "none", $ordered = 0, $type = "1")
{
    return \Semiorbit\Support\Str::Nl2Li( $str, $css_class, $ordered, $type );
}

function str2console($str, $now = false)
{
    if ($now) {
        echo "<script type='text/javascript'>\n";
        echo "//<![CDATA[\n";
        echo "console.log(", json_encode($str), ");\n";
        echo "//]]>\n";
        echo "</script>";
    } else {
        register_shutdown_function('str2console', $str, true);
    }
}

function html_email($to, $subject, $msg, $from = "")
{

    $subject = strip_tags(trim($subject));

    //HEADERS
    $headers = "From: <" . trim($from) . ">\r\n";
    $headers .= "Bcc: " . $from;
    $headers .= "Reply-To: " . trim($from) . "\r\n";
    $headers .= "Return-path: " . trim($from);
    $headers .= "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=utf-8\r\n";

    if (@mail($to, $subject, $msg, $headers)) {
        return true;
    } else {
        return false;
    }

}

//////////////////////////////////////////////////////////////////////////////////////////FROM V2.X
///////////////////////////////////////////////////////////////////////////////////////////////////

function count_visible($grp)
{
    $cnt = 0;
    foreach ($grp['items'] as $k => $fld) {

        if ($fld['control'] == 'none') continue;
        if ($fld['view'] == 'none') continue;
        $cnt++;
    }

    return $cnt;
}











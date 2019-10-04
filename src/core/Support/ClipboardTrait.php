<?php
/*
*------------------------------------------------------------------------------------------------
* CLIPBOARD TRAIT 							 					 semiorbit.com
*------------------------------------------------------------------------------------------------
*
*/

namespace Semiorbit\Support;




trait ClipboardTrait
{

    /**
     * Clipboard is a mechanism to quickly cache variables and reuse them
     *
     * @param mixed $key      var name
     * @param mixed $value    if not NULL, function will set the value and then returns it.
     * @param int|null $clear if Clipboard::CLEAR it will unset the var from cache.<br/>
     *                        if Clipboard::SET_NULL it will set var value to null.<br/>
     *                        if Clipboard::CLEAR_ALL it will Clear cache.<br/>
     *                        if Clipboard::KEEP no action will be done.
     *
     * @return mixed returns value from cache array by var name as key, or NULL if not found.
     */

    public static function &Clipboard($key = null, $value = null, $clear = Clipboard::KEEP)
    {
        static $_Clipboard = array();

        if ($value == null && $clear == null) return $_Clipboard[$key];

        if ($value != null && !is_empty($key)) $_Clipboard[$key] = $value;

        if ($clear === Clipboard::SET_NULL) $_Clipboard[$key] = null;

        if ($clear === Clipboard::CLEAR) unset($_Clipboard[$key]);

        if ($clear === Clipboard::CLEAR_ALL) $_Clipboard = array();

        if ($key === null) return $_Clipboard;


        return $_Clipboard[$key];
    }

}

interface Clipboard
{
    const KEEP = 0;

    const CLEAR = 1;

    const CLEAR_ALL = 2;

    const SET_NULL = null;

}
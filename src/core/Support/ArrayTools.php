<?php

namespace Semiorbit\Support;

class ArrayTools
{

    /**
     * @param array $array Array to order, e.g. ['email' => 'example@domin.tld', 'address' => 'some address', 'name' => 'person name']
     * @param array $order _List of keys in correct order. e.g. ['name', 'email', 'address']
     */

    public static function CustomSort(array &$array, array $order)
    {
        uksort($array, function($key1, $key2) use ($order) {
            return ((array_search($key1, $order) > array_search($key2, $order)) ? 1 : -1);
        });
    }


    /**
     * Escape every string value in a flat array for insertion into HTML.
     *
     * Applies Str::EscapeHtml() to each value using the supplied options.
     * Original array keys and element order are preserved.
     *
     * This method does not process nested arrays. Pass only strings intended
     * for HTML output; prepare boolean flags, loop arrays, and intentional
     * HTML markup separately.
     *
     * @param array<array-key, string> $values The text values to escape.
     * @param int $flags Bitmask of htmlspecialchars() flags. Defaults to ENT_QUOTES | ENT_SUBSTITUTE.
     * @param string $encoding Character encoding of the input. Defaults to UTF-8.
     * @param bool $double_encode Whether to encode existing HTML entities again.
     *                            Set to false to preserve recognized entities.
     *
     * @return array<array-key, string> The escaped values with their original keys.
     *
     * @see Str::EscapeHtml()
     */

    public static function EscapeHtml(array $values, int $flags = ENT_QUOTES | ENT_SUBSTITUTE, string $encoding = 'UTF-8', bool $double_encode = true): array
    {

        return array_map(

            static fn(string $value): string => Str::EscapeHtml($value, $flags, $encoding, $double_encode),

            $values

        );

    }


}
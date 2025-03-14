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

}
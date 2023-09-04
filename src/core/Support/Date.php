<?php

namespace Semiorbit\Support;

class Date
{


    /**
     * Returns first second of a day.
     * <b>Useful with date "BETWEEN" filter condition in SQL</b>
     *
     * @param string $date date without time
     * @return string datetime string in "Y-m-d H:i:s" format (00:00:00)
     */

    public static function DayStart(string $date): string
    {
        return $date . ' 00:00:00';
    }


    /**
     * Returns last second of a day.
     * <b>Useful with date "BETWEEN" filter condition in SQL</b>
     *
     * @param string $date date without time
     * @return string datetime string in "Y-m-d H:i:s" format (23:59:59)
     */

    public static function DayEnd(string $date): string
    {
        return $date . ' 23:59:59';
    }

}
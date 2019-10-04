<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Semiorbit\Console\CliOutput;


/**
 * Helper is the base class for all helper classes.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @see Helper class is derived from <b>Symfony/Console</b>
 */

abstract class Helper
{

    /**
     * Returns the length of a string, using mb_strwidth if it is available.
     *
     * @return int The length of the string
     */

    public static function Strlen(?string $string)
    {

        if (false === $encoding = mb_detect_encoding($string, null, true)) {

            return \strlen($string);

        }


        return mb_strwidth($string, $encoding);

    }

    /**
     * Returns the subset of a string, using mb_substr if it is available.
     *
     * @return string The string subset
     */

    public static function Substr(string $string, int $from, int $length = null)
    {

        if (false === $encoding = mb_detect_encoding($string, null, true)) {

            return substr($string, $from, $length);

        }


        return mb_substr($string, $from, $length, $encoding);

    }



    public static function FormatTime($secs)
    {

        static $timeFormats = [

            [0, '< 1 sec'],

            [1, '1 sec'],

            [2, 'secs', 1],

            [60, '1 min'],

            [120, 'mins', 60],

            [3600, '1 hr'],

            [7200, 'hrs', 3600],

            [86400, '1 day'],

            [172800, 'days', 86400],

        ];

        foreach ($timeFormats as $index => $format) {

            if ($secs >= $format[0]) {

                if ((isset($timeFormats[$index + 1]) && $secs < $timeFormats[$index + 1][0])

                    || $index == \count($timeFormats) - 1

                ) {

                    if (2 == \count($format)) {

                        return $format[1];

                    }


                    return floor($secs / $format[2]).' '.$format[1];

                }

            }

        }

        return null;

    }

    public static function FormatMemory(int $memory)
    {

        if ($memory >= 1024 * 1024 * 1024) {

            return sprintf('%.1f GiB', $memory / 1024 / 1024 / 1024);

        }


        if ($memory >= 1024 * 1024) {

            return sprintf('%.1f MiB', $memory / 1024 / 1024);

        }


        if ($memory >= 1024) {

            return sprintf('%d KiB', $memory / 1024);

        }


        return sprintf('%d B', $memory);

    }



    public static function StrlenWithoutDecoration(OutputFormatter $formatter, $string)
    {
        return self::Strlen(self::RemoveDecoration($formatter, $string));
    }


    public static function RemoveDecoration(OutputFormatter $formatter, $string)
    {

        $isDecorated = $formatter->IsDecorated();

        $formatter->setDecorated(false);

        // remove <...> formatting

        $string = $formatter->Format($string);

        // remove already formatted characters

        $string = preg_replace("/\033\[[^m]*m/", '', $string);

        $formatter->setDecorated($isDecorated);

        return $string;

    }

}
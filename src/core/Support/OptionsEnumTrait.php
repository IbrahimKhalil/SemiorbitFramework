<?php

namespace Semiorbit\Support;

/**
 * @implements OptionsEnumInterface
 */
trait OptionsEnumTrait
{


    /**
     * Convert enum to [NAME => Label] array
     */
    public static function ToArray(): array
    {

        $result = [];

        foreach (static::cases() as $case) {

            $result[$case->name] = $case->Label();

        }

        return $result;

    }

    /**
     * Convert enum to [NAME => Link] array
     */
    public static function ToLinkArray(): array
    {

        $result = [];

        foreach (static::cases() as $case) {

            $result[$case->name] = $case->Link();

        }

        return $result;

    }


    /**
     * Convert enum to [NAME => ['label' => ..., 'link' => ...]] array
     */
    public static function ToFullArray(): array
    {
        $result = [];

        foreach (static::cases() as $case) {

            $result[$case->name] = [

                'label' => $case->Label(),

                'link'  => $case->Link(),

            ];

        }

        return $result;

    }

}

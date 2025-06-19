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

            $result[$case->value] = $case->Label();

        }

        return $result;

    }

    public static function ToArraySubset(array $options): array
    {

        $result = [];

        foreach (static::cases() as $case) {

            if (in_array($case, $options, true)) {

                $result[$case->value] = $case->Label();

            }

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

            $result[$case->value] = $case->Link();

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

            $result[$case->value] = [

                'name' => $case->name,

                'label' => $case->Label(),

                'link'  => $case->Link(),

            ];

        }

        return $result;

    }

}

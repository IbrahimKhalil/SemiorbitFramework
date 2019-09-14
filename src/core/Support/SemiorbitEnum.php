<?php
/**
 * Created by PhpStorm.
 * User: Ibrahim Khalil
 * Date: 01/08/2018
 * Time: 04:40 م
 */

namespace Semiorbit\Support;


use Semiorbit\Translation\Lang;

abstract class SemiorbitEnum
{

    final public function __construct($value)
    {

        $c = new ReflectionClass($this);

        if(!in_array($value, $c->getConstants())) {

            throw IllegalArgumentException();

        }

        $this->value = $value;

    }


    final public function __toString()
    {
        return $this->value;
    }


    public static function OptionsArray($group = null)
    {

        $options = [];


        foreach (static::getConstants() as  $name => $key) {

            if ($group && (!starts_with($name, $group . '_'))) continue;

            $options[$key] = static::Trans($key);
        }

        return $options;

    }

    public static function getConstants() {

        $enumClass = new \ReflectionClass(static::class);

        return $enumClass->getConstants();

    }

    public static function Trans($key)
    {
        return Lang::Trans('enum/' . Str::ParamCase(Path::ClassShortName(static::class)) . '.' . $key);
    }


}
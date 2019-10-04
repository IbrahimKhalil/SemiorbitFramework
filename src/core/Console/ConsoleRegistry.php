<?php


namespace Semiorbit\Console;


class ConsoleRegistry
{
    protected static $_Reg;

    public static function Register(array $Commands = [])
    {

        foreach ($Commands as $command => $class)

            static::RegisterCommand($command, $class);

    }

    /**
     * @param $command
     * @return Command
     */

    public static function FindCommand($command)
    {
        return static::$_Reg[$command] ?? null;
    }

    public static function ListCommands()
    {
        return static::$_Reg;
    }

    public static function RegisterCommand($command, $class)
    {
        static::$_Reg[$command] = $class;
    }

}
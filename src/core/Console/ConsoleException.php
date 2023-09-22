<?php


namespace Semiorbit\Console;


use Semiorbit\Debug\AppRuntimeException;

class ConsoleException extends AppRuntimeException
{

    const CONSOLE_ERR = "Console Error";


    public static function InvalidFlagName($name)
    {
        throw new static(3001, sprintf('Flag "%s" should be one char length', $name));
    }


    public static function UnknownArgument($arg, $defined_list)
    {

        if (count($defined_list)) {

            throw new static(3004, sprintf('Too many arguments, expected arguments "%s".', implode('" "', array_keys($defined_list))));

        }

        throw new static(3009, sprintf('No arguments expected, Unknown argument "%s".', $arg));

    }

    public static function InappropriateArgumentCall(Argument $def, int $type_option)
    {
        throw new static(3013, sprintf('Inappropriate argument call. Argument "%s" was defined as type:{%s} and called as type:{%s}', $def->Name(), $def->Type(), $type_option));
    }

    public static function InvalidArrayArgument(Argument $arg)
    {
        throw new static(3018, sprintf('Argument "%s" can not be set as array. Only last argument in arguments definition list of type:{1} can be set as array.', $arg->Name()));
    }

    public static function InvalidRequiredArgument(Argument $arg)
    {
        throw new static(3023, sprintf('Can not change required value for Argument "%s". Arguments of type:{1} are always required, while flags of type:{3} are optional.', $arg->Name()));
    }

    public static function MissingArgument(Argument $arg)
    {
        throw new static(3027, sprintf('Missing argument "%s"', $arg->Name()));
    }

    public static function MissingOption(Argument $arg)
    {
        throw new static(3032, sprintf('Missing required option "%s"', $arg->Name()));
    }

    public static function DuplicateOptionInput(Argument $arg)
    {
        throw new static(3036, sprintf('Duplicate option entry for "%s". Only array arguments/options can be duplicated in command input.', $arg->Name()));
    }

    public static function NoDefaultValue(Argument $arg)
    {
        throw new static(3041, sprintf('Can not set default value for argument "%s" of type:{%s}. Only options have default value.', $arg->Name(), $arg->Type()));
    }

    public static function LockedList($list)
    {
        throw new static(3045, sprintf('
        
        Forbidden! "%s" is locked. 
        
        Definition list is locked when running command. 
        
        Arguments list is locked while definition list is unlocked and right before Execute is called
        
        
        ', $list));
    }

    public static function InvalidArgumentSignature($signature)
    {
        throw new static(3050, sprintf('Invalid argument signature "%s".', $signature));
    }

    public static function InvalidCommand($command_name)
    {
        throw new static(3055, sprintf('Invalid Command: Command name ["%s"] is not registered or command does not exist.', $command_name));
    }

    public static function MissingCommand()
    {
        throw new static(3060, sprintf('Missing Command! Please enter command name.'));
    }




}
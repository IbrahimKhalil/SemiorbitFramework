<?php


namespace Semiorbit\Console;


use Semiorbit\Base\Application;

class Console
{

    public static function RunCli()
    {

        global $argv;

        $command_name = $argv[1] ?? Application::Abort(404, 'Please enter command');


        //$command

        $output = '';

        $command_fq = ConsoleRegistry::FindCommand($command_name);

        if ($command_fq && class_exists($command_fq)) {


            $command = new $command_fq();

            /** @var Command $command */

            $output = $command->Run();

        } else

            Application::Abort(404);


        echo $output;
    }


    public static function IsCli()
    {

        static $is_cli;

        if ($is_cli !== null) return $is_cli;

        return PHP_SAPI === 'cli';

    }

}
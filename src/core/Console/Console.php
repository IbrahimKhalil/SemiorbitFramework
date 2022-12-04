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

            try {

                $output = $command->Run();

            } catch (\Exception $exception) {

               $command->ExceptionHandle($exception);
               
            }

        } else

            Application::Abort(404);


        echo is_string($output) ? $output : print_r($output);
        
    }


    public static function IsCli()
    {

        static $is_cli;

        if ($is_cli !== null) return $is_cli;

        return PHP_SAPI === 'cli';

    }

}
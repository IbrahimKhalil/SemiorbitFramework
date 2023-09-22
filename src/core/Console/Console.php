<?php


namespace Semiorbit\Console;


use Semiorbit\Base\Application;

class Console
{

    public static function RunCli()
    {

        global $argv;

        $output = '';

        try {

            $command_name = $argv[1] ?? ConsoleException::MissingCommand();

            if ($command_name === '?') { Help::CommandListCli(); goto OUTPUT_LN; }

            //$command

            $command_fq = ConsoleRegistry::FindCommand($command_name);

            if ($command_fq && class_exists($command_fq)) {


                $command = new $command_fq();

                /** @var Command $command */

                try {

                    $output = $command->Run();

                } catch (\Exception $exception) {

                    $command->ExceptionHandle($exception);

                }

            } else {

                ConsoleException::InvalidCommand($command_name);

            }


        } catch (\Exception $exception) {

            $cli =  new Cli();

            $registered_commands = ConsoleRegistry::ListCommandsNames();

            $cn_str = '';

            foreach ($registered_commands as $k => $cn)

                $cn_str .= '<mark>' . ($k+1) . '-</mark> <info>' . $cn . '</info>  ';


            $cli->Writeln('<question>Semiorbit Framework Console</question>');

            $cli->Writeln('');

            $cli->Writeln($cn_str);

            $cli->Writeln('Error: ' . $exception->getCode());

            $cli->Writeln('<error>' . $exception->getMessage() . '</error>');

            $cli->Writeln('<comment>Help:</comment> To get detailed list of commands run: <question>php sc ?</question>');

        }

        OUTPUT_LN:

        echo is_string($output) ? $output : print_r($output);
        
    }


    public static function IsCli()
    {

        static $is_cli;

        if ($is_cli !== null) return $is_cli;

        return PHP_SAPI === 'cli';

    }

}
<?php

namespace Semiorbit\Console;

use Semiorbit\Console\Command;

class Help
{

    public static function CommandListCli()
    {

        $cli = new Cli();

        $list = ConsoleRegistry::ListCommands();

        $n =  1;

        foreach ($list as $cn => $command_fq) {

            $command = new $command_fq();

            /** @var Command $command */

            $cli->Writeln('<info>' . ($n++) . '-</info> <mark>' . $cn . '</mark>');

            $cli->Writeln('<question>' . $command->Signature() . '</question>');

            $cli->Writeln($command_fq);

            $cli->Writeln('');

        }

    }

}
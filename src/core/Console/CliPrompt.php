<?php


namespace Semiorbit\Console;


trait CliPrompt
{


    public function Ask($question)
    {
        return $this->Suggest($question, [], '', 2, false);
    }

    public function Confirm($question)
    {
        return $this->Choice($question . ' [y/n]', ['y'=>'yes', 'n'=>'no'], 'n', 3, false);
    }

    public function Choice($question, array $options, $default=null, $attempts = 3, $print_options = true)
    {
       return $this->Suggest($question, $options, $default, $attempts, $print_options, true);
    }


    public function Suggest($question, array $options, $default=null, $attempts = 3, $print_options = true, $strict = false)
    {


        /** @var Cli $this */

        $response = $default;

        while ($attempts--) {

            $this->Writeln($question);

            if ($options && $print_options) $this->PrintOptionsList($options);

            $response = trim($this->ReadLine());

            if ($response !== '') {

                if ($strict && isset($options[$response])) break;

                elseif (! $strict) break;

            } else $response = $default;

        }

        return $response;

    }

    public function PrintOptionsList(array $options)
    {

        /** @var Cli $this */

        foreach ($options as $key => $value)

            $this->Writeln("  [{$key}] {$value}");

    }




}
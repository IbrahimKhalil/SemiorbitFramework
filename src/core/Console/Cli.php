<?php


namespace Semiorbit\Console;


use Semiorbit\Console\CliOutput\CliOutput;

class Cli extends CliOutput
{

    private $_InputStream;


    use CliPrompt;


    public function InputStream()
    {

        if ($this->_InputStream)

            return $this->_InputStream;

        return $this->_InputStream = fopen ("php://stdin","r");

    }

    public function ReadLine()
    {
        return fgets($this->InputStream());
    }




}
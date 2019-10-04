<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Semiorbit\Console\CliOutput;

/**
 * ConsoleOutput is the default class for all CLI output. It uses STDOUT and STDERR.
 *
 * This class is a convenient wrapper around `StreamOutput` for both STDOUT and STDERR.
 *
 *     $output = new ConsoleOutput();
 *
 * This is equivalent to:
 *
 *     $output = new StreamOutput(fopen('php://stdout', 'w'));
 *     $stdErr = new StreamOutput(fopen('php://stderr', 'w'));
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @see ConsoleOutput class is derived from <b>Symfony/Console</b>
 */

class ConsoleOutput extends StreamOutput
{
  
    private $stderr;

    private $consoleSectionOutputs = [];

    /**
     * @param int $verbosity The verbosity level (one of the VERBOSITY constants in Output)
     * @param bool|null $decorated Whether to decorate messages (null for auto-guessing)
     * @param OutputFormatter|null $formatter Output formatter instance (null to use default OutputFormatter)
     */

    public function __construct(int $verbosity = self::VERBOSITY_NORMAL, bool $decorated = null, OutputFormatter $formatter = null)
    {

        parent::__construct($this->OpenOutputStream(), $verbosity, $decorated, $formatter);


        $actualDecorated = $this->IsDecorated();

        $this->stderr = new StreamOutput($this->OpenErrorStream(), $verbosity, $decorated, $this->getFormatter());

        if (null === $decorated) {

            $this->setDecorated($actualDecorated && $this->stderr->IsDecorated());

        }

    }

    /**
     * Creates a new output section.
     */

    public function Section(): ConsoleSectionOutput
    {
        return new ConsoleSectionOutput($this->getStream(), $this->consoleSectionOutputs, $this->getVerbosity(), $this->IsDecorated(), $this->getFormatter());
    }



    public function setDecorated(bool $decorated)
    {
        parent::setDecorated($decorated);

        $this->stderr->setDecorated($decorated);
    }


    public function setFormatter(OutputFormatter $formatter)
    {
        parent::setFormatter($formatter);

        $this->stderr->setFormatter($formatter);
    }


    public function setVerbosity(int $level)
    {
        parent::setVerbosity($level);

        $this->stderr->setVerbosity($level);
    }


    public function getErrorOutput()
    {
        return $this->stderr;
    }


    public function setErrorOutput(Output $error)
    {
        $this->stderr = $error;
    }

    /**
     * Returns true if current environment supports writing console output to
     * STDOUT.
     *
     * @return bool
     */

    protected function HasStdoutSupport()
    {
        return false === $this->IsRunningOS400();
    }

    /**
     * Returns true if current environment supports writing console output to
     * STDERR.
     *
     * @return bool
     */

    protected function HasStderrSupport()
    {
        return false === $this->IsRunningOS400();
    }

    /**
     * Checks if current executing environment is IBM iSeries (OS400), which
     * doesn't properly convert character-encodings between ASCII to EBCDIC.
     */

    private function IsRunningOS400(): bool
    {

        $checks = [

            \function_exists('php_uname') ? php_uname('s') : '',

            getenv('OSTYPE'),

            PHP_OS,

            ];


        return false !== stripos(implode(';', $checks), 'OS400');

    }

    /**
     * @return resource
     */

    private function OpenOutputStream()
    {

        if (!$this->HasStdoutSupport()) {

            return fopen('php://output', 'w');

        }

        return @fopen('php://stdout', 'w') ?: fopen('php://output', 'w');

    }

    /**
     * @return resource
     */

    private function OpenErrorStream()
    {
        return fopen($this->HasStderrSupport() ? 'php://stderr' : 'php://output', 'w');
    }


}
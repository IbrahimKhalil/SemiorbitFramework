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
 * Base class for output classes.
 *
 * There are five levels of verbosity:
 *
 *  * normal: no option passed (normal output)
 *  * verbose: -v (more output)
 *  * very verbose: -vv (highly extended output)
 *  * debug: -vvv (all debug output)
 *  * quiet: -q (no output)
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @see ConsoleOutput class is derived from <b>Symfony/Console</b>
 */

abstract class Output
{

    const VERBOSITY_QUIET = 16;

    const VERBOSITY_NORMAL = 32;

    const VERBOSITY_VERBOSE = 64;

    const VERBOSITY_VERY_VERBOSE = 128;

    const VERBOSITY_DEBUG = 256;

    const OUTPUT_NORMAL = 1;

    const OUTPUT_RAW = 2;

    const OUTPUT_PLAIN = 4;


    private $verbosity;

    private $formatter;


    /**
     * @param int $verbosity The verbosity level (one of the VERBOSITY constants in OutputInterface)
     * @param bool $decorated Whether to decorate messages
     * @param OutputFormatter|null $formatter Output formatter instance (null to use default OutputFormatter)
     */


    public function __construct(?int $verbosity = self::VERBOSITY_NORMAL, bool $decorated = false, OutputFormatter $formatter = null)
    {

        $this->verbosity = null === $verbosity ? self::VERBOSITY_NORMAL : $verbosity;

        $this->formatter = $formatter ?: new OutputFormatter();

        $this->formatter->setDecorated($decorated);

    }


    public function setFormatter(OutputFormatter $formatter)
    {
        $this->formatter = $formatter;
    }


    /**
     * Returns current output formatter instance.
     *
     * @return OutputFormatter
     */

    public function getFormatter()
    {
        return $this->formatter;
    }


    /**
     * Sets the decorated flag.
     */

    public function setDecorated(bool $decorated)
    {
        $this->formatter->setDecorated($decorated);
    }


    /**
     * Gets the decorated flag.
     *
     * @return bool true if the output will decorate messages, false otherwise
     */

    public function IsDecorated()
    {
        return $this->formatter->IsDecorated();

    }


    /**
     * Sets the verbosity of the output.
     */

    public function setVerbosity(int $level)
    {
        $this->verbosity = $level;
    }



    /**
     * Gets the current verbosity of the output.
     *
     * @return int The current level of verbosity (one of the VERBOSITY constants)
     */

    public function getVerbosity()
    {
        return $this->verbosity;
    }


    /**
     * Returns whether verbosity is quiet (-q).
     *
     * @return bool true if verbosity is set to VERBOSITY_QUIET, false otherwise
     */

    public function IsQuiet()
    {
        return self::VERBOSITY_QUIET === $this->verbosity;
    }


    /**
     * Returns whether verbosity is verbose (-v).
     *
     * @return bool true if verbosity is set to VERBOSITY_VERBOSE, false otherwise
     */

    public function IsVerbose()
    {
        return self::VERBOSITY_VERBOSE <= $this->verbosity;
    }


    /**
     * Returns whether verbosity is very verbose (-vv).
     *
     * @return bool true if verbosity is set to VERBOSITY_VERY_VERBOSE, false otherwise
     */

    public function IsVeryVerbose()
    {
        return self::VERBOSITY_VERY_VERBOSE <= $this->verbosity;

    }


    /**
     * Returns whether verbosity is debug (-vvv).
     *
     * @return bool true if verbosity is set to VERBOSITY_DEBUG, false otherwise
     */

    public function IsDebug()
    {
        return self::VERBOSITY_DEBUG <= $this->verbosity;
    }


    /**
     * Writes a message to the output and adds a newline at the end.
     *
     * @param string|iterable $messages The message as an iterable of strings or a single string
     * @param int $options A bitmask of options (one of the OUTPUT or VERBOSITY constants), 0 is considered the same as self::OUTPUT_NORMAL | self::VERBOSITY_NORMAL
     */

    public function Writeln($messages, int $options = self::OUTPUT_NORMAL)
    {
        $this->Write($messages, true, $options);
    }



    /**
     * Writes a message to the output.
     *
     * @param string|iterable $messages The message as an iterable of strings or a single string
     * @param bool $newline Whether to add a newline
     * @param int $options A bitmask of options (one of the OUTPUT or VERBOSITY constants), 0 is considered the same as self::OUTPUT_NORMAL | self::VERBOSITY_NORMAL
     */

    public function Write($messages, bool $newline = false, int $options = self::OUTPUT_NORMAL)
    {

        if (!is_iterable($messages)) {
            $messages = [$messages];

        }

        $types = self::OUTPUT_NORMAL | self::OUTPUT_RAW | self::OUTPUT_PLAIN;

        $type = $types & $options ?: self::OUTPUT_NORMAL;


        $verbosities = self::VERBOSITY_QUIET | self::VERBOSITY_NORMAL | self::VERBOSITY_VERBOSE | self::VERBOSITY_VERY_VERBOSE | self::VERBOSITY_DEBUG;

        $verbosity = $verbosities & $options ?: self::VERBOSITY_NORMAL;


        if ($verbosity > $this->getVerbosity()) {

            return;

        }

        foreach ($messages as $message) {

            switch ($type) {

                case self::OUTPUT_NORMAL:

                    $message = $this->formatter->Format($message);

                    break;

                case self::OUTPUT_RAW:

                    break;

                case self::OUTPUT_PLAIN:

                    $message = strip_tags($this->formatter->Format($message));

                    break;

            }

            $this->DoWrite($message, $newline);

        }
    }

    /**
     * Writes a message to the output.
     */

    abstract protected function DoWrite(string $message, bool $newline);


}
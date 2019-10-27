<?php


namespace Semiorbit\Console\CliOutput;


use Semiorbit\Console\CliOutput\Progress\ProgressBar;
use Semiorbit\Console\CliOutput\Progress\ProgressIndicator;
use Semiorbit\Console\CliOutput\Table\Table;

class CliOutput
{

    private $_Output;


    /**
     * @return ConsoleOutput
     */
    public function Output()
    {

        if ($this->_Output)

            return  $this->_Output;

        return  $this->_Output = new ConsoleOutput();

    }

    /**
     * Writes a message to the output and adds a newline at the end.
     *
     * @param string|iterable $messages The message as an iterable of strings or a single string
     * @param int $options A bitmask of options (one of the OUTPUT or VERBOSITY constants), 0 is considered the same as self::OUTPUT_NORMAL | self::VERBOSITY_NORMAL
     * @return static
     */

    public function Writeln($messages, int $options = ConsoleOutput::OUTPUT_NORMAL)
    {
        $this->Output()->Writeln($messages, $options);

        return $this;
    }


    /**
     * Writes a message to the output.
     *
     * @param string|iterable $messages The message as an iterable of strings or a single string
     * @param bool $newline Whether to add a newline
     * @param int $options A bitmask of options (one of the OUTPUT or VERBOSITY constants), 0 is considered the same as self::OUTPUT_NORMAL | self::VERBOSITY_NORMAL
     */

    public function Write($messages, bool $newline = false, int $options = ConsoleOutput::OUTPUT_NORMAL)
    {
        $this->Output()->Write($messages, $options);

        return $this;
    }

    public function Table()
    {
        return new Table($this->Output());
    }

    /**
     * @param int $max Maximum steps (0 if unknown)
     * @param float $min_seconds_between_redraws
     * @return ProgressBar
     */

    public function ProgressBar(int $max = 0, float $min_seconds_between_redraws = 0.1)
    {
        return new ProgressBar($this->Output()->Section(), $max, $min_seconds_between_redraws);
    }


    /**
     * @param string|null $format
     * @param int $indicator_change_interval Change interval in milliseconds
     * @param array|null $indicator_values Animated indicator characters
     * @return ProgressIndicator
     */

    public function ProgressIndicator(string $format = null, int $indicator_change_interval = 100, array $indicator_values = null)
    {
        return new ProgressIndicator($this->Output(), $format, $indicator_change_interval, $indicator_values);
    }


    /**
     * Initializes output formatter style.
     *
     * @param string $text
     * @param string|null $foreground The style foreground color name
     * @param string|null $background The style background color name
     * @param array $options
     * @return OutputFormatterStyle
     */

    public function Style(string $text, string $foreground = null, string $background = null, array $options = [])
    {
        return $this->FormatterStyle($foreground, $background, $options)->setText($text);
    }

    public function DefineStyle(string $name, OutputFormatterStyle $style = null)
    {

        if (! $style)

            $style = new OutputFormatterStyle();

        $this->Output()->getFormatter()->setStyle($name, $style);

        return $style;

    }


    /**
     * Initializes output formatter style.
     *
     * @param string|null $foreground The style foreground color name
     * @param string|null $background The style background color name
     * @param array $options
     * @return OutputFormatterStyle
     */

    public function FormatterStyle(string $foreground = null, string $background = null, array $options = []): OutputFormatterStyle
    {
        return new OutputFormatterStyle($foreground, $background, $options);
    }


}
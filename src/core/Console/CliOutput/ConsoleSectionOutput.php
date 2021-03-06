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

use Semiorbit\Console\Terminal;

/**
 * @author Pierre du Plessis <pdples@gmail.com>
 * @author Gabriel Ostrolucký <gabriel.ostrolucky@gmail.com>
 * @see ConsoleOutput class is derived from <b>Symfony/Console</b>
 */
class ConsoleSectionOutput extends StreamOutput
{
    
    private $content = [];

    private $lines = 0;

    private $sections;

    private $terminal;

    /**
     * @param resource $stream
     * @param ConsoleSectionOutput[] $sections
     */

    public function __construct($stream, array &$sections, int $verbosity, bool $decorated, OutputFormatter $formatter)
    {

        parent::__construct($stream, $verbosity, $decorated, $formatter);

        array_unshift($sections, $this);

        $this->sections = &$sections;

        $this->terminal = new Terminal();

    }

    /**
     * Clears previous output for this section.
     *
     * @param int $lines Number of lines to Clear. If null, then the entire output of this section is cleared
     */

    public function Clear(int $lines = null)
    {

        if (empty($this->content) || !$this->IsDecorated()) {

            return;

        }


        if ($lines) {

            array_splice($this->content, -($lines * 2)); // Multiply lines by 2 to cater for each new line added between content

        } else {

            $lines = $this->lines;

            $this->content = [];

        }


        $this->lines -= $lines;


        parent::DoWrite($this->PopStreamContentUntilCurrentSection($lines), false);

    }

    /**
     * Overwrites the previous output with a new message.
     *
     * @param array|string $message
     */

    public function Overwrite($message)
    {

        $this->Clear();

        $this->Writeln($message);

    }


    public function getContent(): string
    {
        return implode('', $this->content);
    }

    /**
     * @internal
     */

    public function AddContent(string $input)
    {

        foreach (explode(PHP_EOL, $input) as $lineContent) {

            $this->lines += ceil($this->getDisplayLength($lineContent) / $this->terminal->getWidth()) ?: 1;

            $this->content[] = $lineContent;

            $this->content[] = PHP_EOL;

        }

    }

    /**
     * {@inheritdoc}
     */

    protected function DoWrite(string $message, bool $newline)
    {

        if (!$this->IsDecorated()) {

            parent::DoWrite($message, $newline);

            return;

        }


        $erasedContent = $this->PopStreamContentUntilCurrentSection();

        $this->AddContent($message);


        parent::DoWrite($message, true);

        parent::DoWrite($erasedContent, false);

    }

    /**
     * At initial stage, cursor is at the end of stream output. This method makes cursor crawl upwards until it hits
     * current section. Then it erases content it crawled through. Optionally, it erases part of current section too.
     */

    private function PopStreamContentUntilCurrentSection(int $numberOfLinesToClearFromCurrentSection = 0): string
    {

        $numberOfLinesToClear = $numberOfLinesToClearFromCurrentSection;

        $erasedContent = [];


        foreach ($this->sections as $section) {

            if ($section === $this) {

                break;
            }


            $numberOfLinesToClear += $section->lines;

            $erasedContent[] = $section->getContent();

        }

        if ($numberOfLinesToClear > 0) {

            // move cursor up n lines

            parent::DoWrite(sprintf("\x1b[%dA", $numberOfLinesToClear), false);

            // erase to end of screen

            parent::DoWrite("\x1b[0J", false);

        }

        return implode('', array_reverse($erasedContent));

    }

    private function getDisplayLength(string $text): string
    {
        return Helper::StrlenWithoutDecoration($this->getFormatter(), str_replace("\t", '        ', $text));
    }

}
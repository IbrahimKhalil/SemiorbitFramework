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
 * Formatter class for console output.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 * @author Roland Franssen <franssen.roland@gmail.com>
 * @see ConsoleOutput class is derived from <b>Symfony/Console</b>
 */

class OutputFormatter
{

    
    private $decorated;

    private $styles = [];

    private $styleStack;


    /**
     * Escapes "<" special char in given text.
     *
     * @return string Escaped text
     */

    public static function Escape(string $text)
    {
        $text = preg_replace('/([^\\\\]?)</', '$1\\<', $text);


        return self::EscapeTrailingBackslash($text);
    }


    /**
     * Escapes trailing "\" in given text.
     *
     * @internal
     */

    public static function EscapeTrailingBackslash(string $text): string
    {

        if ('\\' === substr($text, -1)) {

            $len = \strlen($text);

            $text = rtrim($text, '\\');

            $text = str_replace("\0", '', $text);

            $text .= str_repeat("\0", $len - \strlen($text));

        }


        return $text;

    }



    /**
     * Initializes console output formatter.
     *
     * @param OutputFormatterStyle[] $styles Array of "name => FormatterStyle" instances
     */

    public function __construct(bool $decorated = false, array $styles = [])
    {

        $this->decorated = $decorated;


        $this->setStyle('error', new OutputFormatterStyle('white', 'red'));

        $this->setStyle('info', new OutputFormatterStyle('green'));

        $this->setStyle('comment', new OutputFormatterStyle('yellow'));

        $this->setStyle('question', new OutputFormatterStyle('black', 'cyan'));


        foreach ($styles as $name => $style) {

            $this->setStyle($name, $style);

        }


        $this->styleStack = new OutputFormatterStyleStack();


    }



    public function setDecorated(bool $decorated)
    {
        $this->decorated = $decorated;
    }


    public function IsDecorated()
    {
        return $this->decorated;
    }


    public function setStyle(string $name, OutputFormatterStyle $style)
    {
        $this->styles[strtolower($name)] = $style;
    }


    public function HasStyle(string $name)
    {
        return isset($this->styles[strtolower($name)]);
    }


    public function getStyle(string $name)
    {

        if (!$this->HasStyle($name)) {

            throw new \RunTimeException(sprintf('Undefined style: %s', $name));

        }

        return $this->styles[strtolower($name)];

    }


    public function Format(?string $message)
    {
        return $this->FormatAndWrap($message, 0);
    }


    public function FormatAndWrap(?string $message, int $width)
    {

        $offset = 0;

        $output = '';

        $tagRegex = '[a-z][^<>]*+';

        $currentLineLength = 0;

        preg_match_all("#<(($tagRegex) | /($tagRegex)?)>#ix", $message, $matches, PREG_OFFSET_CAPTURE);


        foreach ($matches[0] as $i => $match) {

            $pos = $match[1];

            $text = $match[0];


            if (0 != $pos && '\\' == $message[$pos - 1]) {

                continue;

            }

            // add the text up to the next tag
            $output .= $this->ApplyCurrentStyle(substr($message, $offset, $pos - $offset), $output, $width, $currentLineLength);

            $offset = $pos + \strlen($text);


            // opening tag?

            if ($open = '/' != $text[1]) {

                $tag = $matches[1][$i][0];

            } else {

                $tag = isset($matches[3][$i][0]) ? $matches[3][$i][0] : '';

            }

            if (!$open && !$tag) {

                // </>

                $this->styleStack->Pop();

            } elseif (null === $style = $this->CreateStyleFromString($tag)) {

                $output .= $this->ApplyCurrentStyle($text, $output, $width, $currentLineLength);

            } elseif ($open) {

                $this->styleStack->Push($style);

            } else {

                $this->styleStack->Pop($style);

            }

        }


        $output .= $this->ApplyCurrentStyle(substr($message, $offset), $output, $width, $currentLineLength);


        if (false !== strpos($output, "\0")) {

            return strtr($output, ["\0" => '\\', '\\<' => '<']);

        }


        return str_replace('\\<', '<', $output);


    }



    /**
     * @return OutputFormatterStyleStack
     */

    public function getStyleStack()
    {
        return $this->styleStack;
    }



    /**
     * Tries to create new style instance from string.
     */
    private function CreateStyleFromString(string $string): ?OutputFormatterStyle
    {

        if (isset($this->styles[$string])) {

            return $this->styles[$string];

        }


        if (!preg_match_all('/([^=]+)=([^;]+)(;|$)/', $string, $matches, PREG_SET_ORDER)) {

            return null;

        }



        $style = new OutputFormatterStyle();


        foreach ($matches as $match) {

            array_shift($match);

            $match[0] = strtolower($match[0]);


            if ('fg' == $match[0]) {

                $style->setForeground(strtolower($match[1]));

            } elseif ('bg' == $match[0]) {

                $style->setBackground(strtolower($match[1]));

            } elseif ('href' === $match[0]) {

                $style->setHref($match[1]);

            } elseif ('options' === $match[0]) {

                preg_match_all('([^,;]+)', strtolower($match[1]), $options);

                $options = array_shift($options);

                foreach ($options as $option) {

                    $style->setOption($option);

                }

            } else {

                return null;

            }

        }

        return $style;

    }

    /**
     * Applies current style from stack to text, if must be applied.
     */

    private function ApplyCurrentStyle(string $text, string $current, int $width, int &$currentLineLength): string
    {

        if ('' === $text) {

            return '';

        }

        if (!$width) {

            return $this->IsDecorated() ? $this->styleStack->getCurrent()->Apply($text) : $text;

        }


        if (!$currentLineLength && '' !== $current) {

            $text = ltrim($text);

        }


        if ($currentLineLength) {

            $prefix = substr($text, 0, $i = $width - $currentLineLength) . "\n";

            $text = substr($text, $i);

        } else {

            $prefix = '';

        }


        preg_match('~(\\n)$~', $text, $matches);


        $text = $prefix . preg_replace('~([^\\n]{' . $width . '})\\ *~', "\$1\n", $text);

        $text = rtrim($text, "\n") . ($matches[1] ?? '');


        if (!$currentLineLength && '' !== $current && "\n" !== substr($current, -1)) {

            $text = "\n" . $text;

        }


        $lines = explode("\n", $text);


        foreach ($lines as $line) {

            $currentLineLength += \strlen($line);

            if ($width <= $currentLineLength) {

                $currentLineLength = 0;

            }

        }

        if ($this->IsDecorated()) {

            foreach ($lines as $i => $line) {

                $lines[$i] = $this->styleStack->getCurrent()->Apply($line);

            }
        }

        return implode("\n", $lines);

    }

}
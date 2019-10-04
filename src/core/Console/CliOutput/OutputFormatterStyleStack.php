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
 * @author Jean-François Simon <contact@jfsimon.fr>
 * @see OutputFormatterStyle class is derived from <b>Symfony/Console</b>
 */

class OutputFormatterStyleStack
{
    
    
    /**
     * @var OutputFormatterStyle[]
     */

    private $styles;


    private $emptyStyle;


    public function __construct(OutputFormatterStyle $emptyStyle = null)
    {
        $this->emptyStyle = $emptyStyle ?: new OutputFormatterStyle();

        $this->Reset();
    }


    /**
     * Resets stack (ie. empty internal arrays).
     */

    public function Reset()
    {
        $this->styles = [];

    }

    /**
     * Pushes a style in the stack.
     */
    public function Push(OutputFormatterStyle $style)
    {
        $this->styles[] = $style;
    }


    /**
     * Pops a style from the stack.
     *
     * @return OutputFormatterStyle
     *
     * @throws \RunTimeException When style tags incorrectly nested
     */

    public function Pop(OutputFormatterStyle $style = null)
    {
        if (empty($this->styles)) {
            return $this->emptyStyle;

        }

        if (null === $style) {
            return array_pop($this->styles);

        }

        foreach (array_reverse($this->styles, true) as $index => $stackedStyle) {
            if ($style->Apply('') === $stackedStyle->apply('')) {
                $this->styles = \array_slice($this->styles, 0, $index);


                return $stackedStyle;

            }
        }

        throw new \RunTimeException('Incorrectly nested style tag found.');

    }


    /**
     * Computes current style with stacks top codes.
     *
     * @return OutputFormatterStyle
     */

    public function getCurrent()
    {
        if (empty($this->styles)) {
            return $this->emptyStyle;

        }

        return $this->styles[\count($this->styles) - 1];

    }



    /**
     * @return $this
     */

    public function setEmptyStyle(OutputFormatterStyle $emptyStyle)
    {
        $this->emptyStyle = $emptyStyle;


        return $this;
    }

    /**
     * @return OutputFormatterStyle
     */
    public function getEmptyStyle()
    {
        return $this->emptyStyle;
    }


}
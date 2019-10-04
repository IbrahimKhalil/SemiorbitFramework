<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Semiorbit\Console\CliOutput\Table;


/**
 * @internal
 * @see TableRows class is derived from <b>Symfony/Console</b>
 */
class TableRows implements \IteratorAggregate
{
    private $generator;

    public function __construct(callable $generator)
    {
        $this->generator = $generator;
    }

    public function getIterator(): \Traversable
    {
        $g = $this->generator;

        return $g();
    }

}

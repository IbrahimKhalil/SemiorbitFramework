<?php

namespace Semiorbit\Debug;



/**
 * Common contract for integer-backed error enums.
 */

interface ErrorEnumInterface
{

    public function Code(): int;



    /**
     * Return a JSON language map containing English and the active locale.
     */

    public function Label(): string;

}
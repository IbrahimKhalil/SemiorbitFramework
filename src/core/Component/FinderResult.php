<?php
/*
*-----------------------------------------------------------------------------------------------
* FINDER RESULT                         			  						    semiorbit.com
*-----------------------------------------------------------------------------------------------
*
*/

namespace Semiorbit\Component;


use Semiorbit\Support\AltaArray;
use Semiorbit\Support\Path;


class FinderResult extends AltaArray
{
    public $Class;

    public $Path;

    public $Dir;

    public $Selector;
    
    public $Model;


    public function __construct($array)
    {
        parent::__construct($array);
    }

    public function __toString()
    {
        return strval($this->Class) . ' => ' . strval($this->Path);
    }

    public function &__get($key)
    {
        if ( strtolower($key) == "path" && empty( $this->Path ) && ! empty( $this->Class )  ) {

            return $this->Path = Path::ClassFileName($this->Class);

        }

        return parent::__get($key);
    }

}
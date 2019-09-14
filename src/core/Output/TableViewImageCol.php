<?php
/*
*------------------------------------------------------------------------------------------------
* SEMIORBIT - TABLE VIEW COLUMN					 					 semiorbit.com
*------------------------------------------------------------------------------------------------
*/

namespace Semiorbit\Output;



/**
 * Class TableViewImageCol
 *
 * @package Semiorbit\Output
 */
class TableViewImageCol extends TableViewCol
{

    public $Control = TableViewControl::IMAGE;





    public function DefaultHtmlBuilder()
    {
        return $this->ActiveField()->Html();
    }


}
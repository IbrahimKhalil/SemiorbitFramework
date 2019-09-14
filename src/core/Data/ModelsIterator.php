<?php
namespace Semiorbit\Data;


use Semiorbit\Db\Table;

class ModelsIterator extends \ArrayIterator
{

    private $_DataSet;

    public function __construct($value, Table $table)
    {
        parent::__construct($value);

        $this->_DataSet = $table->ActiveDataSet();
    }

    public function current()
    {
        $value = parent::current();

        return $this->_DataSet->Fill($value);
    }

}
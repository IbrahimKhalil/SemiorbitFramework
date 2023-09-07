<?php

namespace Semiorbit\Field;

use Semiorbit\Data\DataSet;
use Semiorbit\Db\DB;

trait Datalist
{


    public array $Datalist = [];

    protected $DatalistSource;


    /**
     * @param array $DataList
     * @return $this
     */
    public function setDatalist(array $DataList)
    {
        $this->Datalist = $DataList;

        return $this;
    }

    /**
     * @param $table
     * @param $field
     * @return $this
     */
    public function setDatalistSource($table, $field)
    {
        $this->DatalistSource = [$table, $field];

        return $this;
    }

    /**
     * @return $this
     */
    public function ClearDatalistSource()
    {
        $this->DatalistSource = null;

        return $this;
    }

    protected function FillDatalist()
    {

        if ($this->DatalistSource) {

            $connection = $this->ActiveDataSet() instanceof DataSet ? $this->ActiveDataSet()->ActiveConnection()

                : DB::ActiveConnection();

            list($table, $field) = $this->DatalistSource;

            $tbl = $connection->Table("SELECT DISTINCT(`{$field}`) FROM `{$table}` ORDER BY `{$field}`");

            while ($row = $tbl->Row()) $this->Datalist[] = $row[$field];

        }

    }

    public function DatalistId(): string
    {
        return "__datalist__" . ($this->InputID ?: $this->InputName());
    }

    protected function AddDatalistAttrs(&$attrs, $autocomplete = 'off')
    {

        if ($this->Datalist) {

            $attrs[Field::LIST] = $this->DatalistId();

            $attrs[Field::AUTO_COMPLETE] = $autocomplete;

        }

    }

}
<?php

namespace Semiorbit\Support;


use Semiorbit\Data\DataSet;
use Semiorbit\Field\Field;
use Semiorbit\Field\Select;

class ExportCsv
{
    protected $_DataSet;

    protected $_Cols = array();

    protected $_FileName;

    protected $_FileExt;


    public function __construct(DataSet $data_set)
    {
        $this->UseDataSet($data_set);

    }

    public static function From(DataSet $data_set)
    {
        return new static($data_set);
    }

    public function UseDataSet(DataSet $data_set)
    {
        $this->_DataSet = $data_set;

        $this->ShowColumns();

        return $this;
    }

    /**
     * @return DataSet
     */
    public function ActiveDataSet()
    {
        return $this->_DataSet;
    }

    public function ShowColumns(...$cols)
    {

        if (empty($cols)) {

            foreach ($this->ActiveDataSet()->Fields() as $col)

                $cols[] = $col;

        }

        $this->_Cols = $cols;

        foreach ($cols As $col) {

            if ($col instanceof Select && $col->ForeignKeyLoadMethod() == Select::FKEY_EAGER_LOADING)

                $col->setForeignKeyLoadMethod(Select::FKEY_LOAD_MISSING);

        }

        return $this;

    }

    public function Columns()
    {
        return $this->_Cols;
    }

    public function setFileName($file_name)
    {
        $this->_FileName = $file_name;

        return $this;
    }

    public function FileName()
    {

        $time = date("-d-m-Y-H-i-s");

        if (empty($this->_FileName)) $this->_FileName = Str::ParamCase( $this->ActiveDataSet()->Name() );

        return $this->_FileName . $time . $this->getFileExt();

    }

    public function getFileExt()
    {
        return $this->_FileExt ?: $this->_FileExt = '.csv';
    }

    /**
     * @param mixed $FileExt
     * @return ExportCsv
     */
    public function setFileExt($FileExt)
    {
        $this->_FileExt = $FileExt;

        return $this;
    }


    public function Download($file_name = null)
    {

        if (!empty($file_name))

            $this->setFileName($file_name);

        $this->SendHeaders()->SendData();

    }

    public function SendHeaders() {

        // disable caching

        $now = gmdate("D, d M Y H:i:s");

        header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");

        header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");

        header("Last-Modified: {$now} GMT");

        // force download

        header("Content-Type: application/force-download");

        header("Content-Type: application/octet-stream");

        header("Content-Type: application/download");

        // disposition / encoding on response body

        header('Content-Type: text/csv; charset=UTF-16LE');

        header("Content-Disposition: attachment;filename={$this->FileName()}");

        header("Content-Transfer-Encoding: binary");

        return $this;

    }


    public function SendData()
    {


        $myData = $this->ActiveDataSet();

        $df = fopen("php://output", 'w');

        fputs( $df, "\xEF\xBB\xBF" );

        //fputs( $df, mb_convert_encoding("sep=;\r\n", "UTF-16LE") );


        $heads = array();

        foreach ($this->Columns() as $col)
        {
            /** @var $col Field */

            $heads[] = $col->LabelText();

        }



        fputcsv($df, $heads,';');

        while ($myData->Read()) {

            $values = array();

            reset($this->_Cols);

            foreach ($this->Columns() as $col)
            {
                /** @var $col Field */

                $values[] = strip_tags( $col->Html() );
            }

            fputcsv($df, $values,';');

        }



        fclose($df);

    }


}
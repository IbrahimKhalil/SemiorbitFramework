<?php

namespace Semiorbit\Field;



use Semiorbit\Support\Binary;

class DataField
{


    public static function IntId($name = 'int_id')
    {

        return Field::ID($name)

            ->setType(DataType::INT)

            ->setAutoIncrement(true)

            ->setMaxLength(11)

            ->setRequired();

    }


    public static function UUID($name)
    {

        return Field::ID($name)

            ->IsUUID_SHORT()

            ->setUnsigned()

            ->setRequired();
    }


    public static function Decimal($name)
    {

        return Field::Number($name)

            ->setType(DataType::DECIMAL)

            ->NumberFormat(2)

            ->setStep('any')

            ->setDefaultValue(0)

            ->UseHtmlBuilder(function ($fld) {

                return floatval($fld->Value);

            });

    }



    public static function Binary($name)
    {

        return Field::Text($name)

            ->UseSelectExpr(Binary::HexHelper($name))

            ->WhereClauseHelper(Binary::UnHexHelper());

    }


    public static function BinaryText($name)
    {

        return Field::Text($name)

            ->setType(DataType::BINARY)

            ->StoreValuePrepareHelper(Binary::Bin2HexStroingHelper())

            ->WhereClauseHelper(Binary::UnHexTextHelper());

    }



    public static function CreationDate($name = 'creation_date')
    {

        return Field::DateTime($name)

            ->setRequired()

            ->setDefaultValue( date("Y-m-d H:i:s") )

            ->NoControl();

    }


    public static function LastUpdate($name = 'last_update')
    {

        return Field::DateTime($name)

            ->setRequired()

            ->setDefaultValue( date("Y-m-d H:i:s") )

            ->setReadOnly()

            ->NoControl();

    }


    public static function Date($name = 'date')
    {
        
        return Field::DateTime($name)

            ->setType(DataType::DATE)

            ->setShowTime(false)

            ->setFormat("%Y-%m-%d");
        
    }


}
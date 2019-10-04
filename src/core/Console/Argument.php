<?php


namespace Semiorbit\Console;


class Argument
{

    const TYPE_ARGUMENT = 1;

    const TYPE_OPTION = 2;

    const TYPE_FLAG = 3;


    protected $_Name;

    protected $_Type = self::TYPE_ARGUMENT;

    protected $_Default;

    protected $_Value;

    protected $_Description;

    protected $_IsRequired = false;

    protected $_IsArray = false;


    private $_IsLocked;

    private $_Definition;


    public function __construct($name, $arg_type = self::TYPE_ARGUMENT)
    {
        $this->_Name = $name;

        $this->setType($arg_type);
    }

    public function UseDefinition($def)
    {

        if ($def instanceof Argument) {

            $this->_Definition = $def;


            $this->setType( $def->Type() );

            $this->setDescription( $def->Description() );

            $this->setIsArray( $def->IsArray() );

            if ($this->_Type === self::TYPE_OPTION) {

                $this->setDefault( $def->Default() );

                $this->setIsRequired( $def->IsRequired() );

            }

        }

        return $this;

    }

    /**
     * @return string
     */
    public function Name()
    {
        return $this->_Name;
    }

    /**
     * @param string $description
     * @return Argument
     */
    public function setDescription($description)
    {
        $this->CheckLocked();

        $this->_Description = $description;

        return $this;
    }

    /**
     * @param mixed $default
     * @return Argument
     */
    public function setDefault($default)
    {

        $this->CheckLocked();


        if ($this->Type() !== Argument::TYPE_OPTION)

            ConsoleException::NoDefaultValue($this);

        $this->_Default = $default;

        return $this;

    }

    /**
     * @return mixed
     */
    public function Default()
    {
        return $this->_Default;
    }

    /**
     * @param mixed $value
     * @param null $index Only if IsArray
     * @return Argument
     */
    public function setValue($value, $index = null)
    {

        $this->CheckLocked();

        if ($this->IsArray()) {

            if (empty($this->_Value) && $value === null) {

                $this->ClearValues();

            } else {

                if (!is_array($this->_Value))

                    $this->_Value = [$this->_Value];

                if ($index === null)

                    $index = (($count = count($this->_Value)) ? $count - 1 : 0);

                $this->_Value[$index] = $value;

            }

        } else {

            $this->_Value = $value;

        }

        $this->ValidateValues();

        return $this;

    }

    /**
     * Set this argument as array and Push new value
     *
     * @param mixed $value
     * @return $this
     */
    public function AddValue($value)
    {

        $this->CheckLocked();


        $this->setIsArray();

        $this->_Value[] = $value;

        $this->ValidateValues();

        return $this;
    }

    /**
     * Set this argument as array and Push new values
     *
     * @param mixed ...$values
     * @return $this
     */
    public function AddValues(...$values)
    {

        $this->CheckLocked();


        $this->_Value = array_merge($this->setIsArray()->_Value, $values);

        $this->ValidateValues();

        return $this;
    }

    public function ClearValues()
    {
        $this->CheckLocked();

        $this->_Value = $this->IsArray() ? [] : null;
    }

    public function UseValues($values)
    {

        $this->CheckLocked();


        if (is_array($values))

            $this->IsArray();

        $this->_Value = $values;

        $this->ValidateValues();

        return $this;

    }

    public function UnsetValue($index)
    {
        $this->CheckLocked();

        if ($this->IsArray()) unset($this->_Value[$index]);

        return $this;
    }

    public function ValidateValues()
    {

        $this->CheckLocked();

        if ($this->IsArray()) {

            if ($this->IsFlag()) {

                $this->_Value = array_map(function ($item) {

                    return boolval($item);

                }, $this->_Value);


            }

        } else {

            if ($this->IsFlag()) $this->_Value = boolval($this->_Value);

        }
    }

    /**
     * @param null $index only if Array
     * @return mixed
     */
    public function Value($index = null)
    {

        if ($index !== null && $this->IsArray())

            return $this->_Value[$index];

        return $this->_Value;
    }


    /**
     * @return string
     */
    public function Description()
    {
        return $this->_Description;
    }

    /**
     * @param bool $is_required
     * @return Argument
     */
    public function setIsRequired(bool $is_required = true): Argument
    {

        $this->CheckLocked();


        if (! $this->IsOption())

            ConsoleException::InvalidRequiredArgument($this);

        $this->_IsRequired = $is_required;
        
        return $this;

    }

    /**
     * @return bool
     */
    public function IsRequired(): bool
    {
        return $this->_IsRequired;
    }

    /**
     * @param int $type
     * @return Argument
     */
    public function setType(int $type): Argument
    {

        $this->CheckLocked();


        $this->_Type = $type;

        if ($type === self::TYPE_ARGUMENT) {

            $this->_Default = null;

            $this->_IsRequired = true;

        } elseif ($type === self::TYPE_FLAG) {

            $this->_Default = null;

            $this->_IsRequired = false;

        }

        return $this;

    }

    /**
     * @return int
     */
    public function Type(): int
    {
        return $this->_Type;
    }

    /**
     * @param bool $is_array
     * @return Argument
     */
    public function setIsArray(bool $is_array = true): Argument
    {

        $this->CheckLocked();

        if ($this->_IsArray && !$is_array) {

            if (count($this->_Value) > 1) return $this;

            $this->_Value = empty($this->_Value) ? null : ($this->_Value[0] ?? null);


        } elseif (!$this->_IsArray && $is_array) {

            $this->_Value = empty($this->_Value) ? [] : [$this->_Value];

        }

        $this->_IsArray = $is_array;

        return $this;

    }

    /**
     * @return bool
     */
    public function IsArray(): bool
    {
        return $this->_IsArray;
    }


    public function IsFlag(): bool
    {
        return $this->Type() === self::TYPE_FLAG;
    }

    public function IsOption(): bool
    {
        return $this->Type() === self::TYPE_OPTION;
    }

    public function IsArgument(): bool
    {
        return $this->Type() === self::TYPE_ARGUMENT;
    }


    /**
     * @param $list_name
     * @param bool $is_locked
     * @return static
     */
    public function setIsLocked($list_name): Argument
    {
        $this->CheckLocked();

        $this->_IsLocked = $list_name;

        return $this;
    }

    /**
     * @return bool
     */
    public function IsLocked()
    {
        return $this->_IsLocked;
    }


    protected function CheckLocked()
    {
        if ($list = $this->IsLocked())

            ConsoleException::LockedList($list);

    }

    /**
     * @return mixed
     */
    public function ActiveDefinition()
    {
        return $this->_Definition;
    }

    public function __debugInfo()
    {

        return [

            'Name' => $this->_Name . ' ===================================================================== ',

            'Type' => $this->_Type,

            'Default' => $this->_Default,

            'Value' => $this->_Value,

            'Description' => $this->_Description,

            'IsRequired' => $this->_IsRequired,

            'IsArray' => $this->_IsArray,

            'IsLocked' => $this->_IsLocked,

            'Def' => $this->_Definition instanceof Argument ? $this->_Definition->Name() : $this->_Definition

        ];

    }


    public function Prefix()
    {

        switch ($this->Type()) {

            case Argument::TYPE_FLAG:

                return '-';
                break;

            case Argument::TYPE_OPTION:

                return '--';
                break;

            case Argument::TYPE_ARGUMENT:
            default:

                return'';
                break;
        }

    }


    public function Signature()
    {
        return

              $this->Prefix()

            . $this->Name()

            . "="

            . ($this->IsArray() ? "[" . implode(", ", $this->Value()) . "]"

                  : ($this->IsFlag() ? (boolval($this->Value()) ? "1" : "0")

                      : $this->Value()))

            . ($this->IsOption() && $this->IsRequired() ? "!" : "")

            . '  : ' .

            $this->Description();

    }

    public function __toString()
    {
        return $this->Signature();
    }

}
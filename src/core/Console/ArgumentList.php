<?php


namespace Semiorbit\Console;


use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

class ArgumentList implements ArrayAccess, Countable, IteratorAggregate
{

    private $_Data = [];


    private $_ArgsByType = [Argument::TYPE_ARGUMENT => [], Argument::TYPE_OPTION => [], Argument::TYPE_FLAG => []];


    private $_IsLocked = false;

    private $_Definition;


    public function Add($name, $arg_type = Argument::TYPE_ARGUMENT): Argument
    {

        $myArgument = new Argument($name, $arg_type);

        $this->offsetSet(null, $myArgument);

        return $this->offsetGet($this->count() - 1);

    }

    public function AddOption($name): Argument
    {
        return $this->Add($name, Argument::TYPE_OPTION);
    }

    public function AddFlag($name): Argument
    {
        return $this->Add($name, Argument::TYPE_FLAG);
    }

    public function Argument($name, $type = null)
    {
        return $this->FindArgument($name, $type) ?: $this->Add($name);
    }

    public function Option($name)
    {
        return $this->FindArgument($name, Argument::TYPE_OPTION) ?: $this->AddOption($name);
    }

    public function Flag($name)
    {
        return $this->FindArgument($name, Argument::TYPE_FLAG) ?: $this->AddFlag($name);
    }

    public function FindArgument($name, $type = null)
    {

        return ($type === null) ?

            ($this->_ArgsByType[Argument::TYPE_ARGUMENT][$name] ??

                $this->_ArgsByType[Argument::TYPE_OPTION][$name] ??

                $this->_ArgsByType[Argument::TYPE_FLAG][$name] ?? false) :

            ($this->_ArgsByType[$type][$name] ?? false);

    }

    protected function CheckLocked()
    {
        if ($list = $this->IsLocked())

            ConsoleException::LockedList($list);

    }


    #region List & Index

    public function HasArgument($index)
    {
        return array_keys($this->ListArguments())[$index] ?? false;
    }

    private function setData($offset, $argument)
    {
        $offset === null ?

            $this->_Data[] = $argument :

            $this->_Data[$offset] = $argument;
    }


    public function ToArray()
    {
        $clone = $this->_Data;

        return $clone;
    }

    /**
     * Lists only argument of type: Argument
     *
     * @return array
     */
    public function ListArguments()
    {
        return $this->ListByType(Argument::TYPE_ARGUMENT);
    }

    public function ListOptions()
    {
        return $this->ListByType(Argument::TYPE_OPTION);
    }

    public function ListFlags()
    {
        return $this->ListByType(Argument::TYPE_FLAG);
    }


    public function ListByType($type = null)
    {
        return $type ? $this->_ArgsByType[$type] : $this->_ArgsByType;
    }

    private function UnsetIndex($name)
    {
        foreach ($this->_ArgsByType as $arr)

            if (isset($arr[$name])) unset($arr[$name]);
    }

    #endregion


    #region Interfaces

    /**
     * Whether a offset exists
     * @link https://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return bool true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return isset($this->_Data[$offset]);
    }

    /**
     * Offset to retrieve
     * @link https://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return $this->_Data[$offset];
    }

    /**
     * Offset to set
     * @link https://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param Argument $argument <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $argument)
    {

        $this->CheckLocked();


        if ($argument instanceof Argument) {


            if ($found = $this->FindArgument($argument->Name())) {

                /** @var Argument $found */

                if ($found->Type() !== $argument->Type())

                    ConsoleException::InappropriateArgumentCall($found, $argument->Type());


                $this->setData($offset, $found);

                $values = $argument->IsArray() ? $argument->Value() : [$argument->Value()];


                $found->AddValues(...$values);


            } else {


                if ($this->ActiveDefinition()) {


                    $def = $this->ActiveDefinition()->FindArgument($argument->Name());


                    if ($def !== $argument->ActiveDefinition()) {


                        if ($def && $def->Type() !== $argument->Type())

                            ConsoleException::InappropriateArgumentCall($def, $argument->Type());


                        $argument->UseDefinition($def);

                    }


                }


                if (isset($this->_Data[$offset]))

                    $this->UnsetIndex($this->_Data[$offset]->Name());


                $this->setData($offset, $argument);

                $this->_ArgsByType[$argument->Type()][$argument->Name()] = $argument;

            }

        }
    }


    /**
     * Offset to unset
     * @link https://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {

        $this->CheckLocked();

        if (isset($this->_Data[$offset]))

            $this->UnsetIndex($this->_Data[$offset]->Name());

        unset($this->_Data[$offset]);

    }

    /**
     * Count elements of an object
     * @link https://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count()
    {
        $count = count($this->_Data);

        return $count;
    }

    /**
     * Retrieve an external iterator
     * @link https://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {


        return new ArrayIterator($this->_Data);
    }


    #endregion

    /**
     * @param $list_name
     * @return ArgumentList
     */
    public function setIsLocked($list_name): ArgumentList
    {

        $this->CheckLocked();


        $this->_IsLocked = $list_name;

        foreach ($this->_Data as $arg)

            /** @var Argument $arg */

            if (!$arg->IsLocked()) $arg->setIsLocked($list_name);

        return $this;

    }

    /**
     * @return bool
     */
    public function IsLocked()
    {
        return $this->_IsLocked;
    }

    public function UseDefinition(ArgumentList $definition)
    {
        $this->_Definition = $definition;
    }


    /**
     * @return ArgumentList
     */
    public function ActiveDefinition()
    {
        return $this->_Definition;
    }


    public function __debugInfo()
    {

        return [

            'Arguments' => $this->_ArgsByType[Argument::TYPE_ARGUMENT],

            'Options' => $this->_ArgsByType[Argument::TYPE_OPTION],

            'Flags' => $this->_ArgsByType[Argument::TYPE_FLAG],

            'Total' => $this->count(),

            'List' => $this->SignatureArray()

        ];
    }

    public function SignatureArray()
    {

        $signature = [];

        foreach ($this->_Data as $arg)

            /** @var Argument $arg */
            $signature[] = $arg->Signature();


        return $signature;

    }

    public function Signature($sep = PHP_EOL)
    {
        return $sep . '{' . implode('} ' . $sep . '{', $this->SignatureArray()) . '}' . $sep;
    }

    public function __toString()
    {
        return $this->Signature('');
    }


    public function ParseSignature($signature)
    {

        if (preg_match_all('/{\s*(.*?)\s*}/', $signature, $matches)) {


            foreach ($matches[1] as $input) {

                // Arg, Description
                // ====================================================================


                $parts = explode(':', $input, 2);

                $arg = trim($parts[0]);

                if (empty($arg))

                    ConsoleException::InvalidArgumentSignature($signature);

                $description = count($parts) > 1 ? trim($parts[1]) : null;


                // Type
                // ====================================================================

                $arg = trim($arg);

                if (starts_with($arg, '--')) {

                    $type = Argument::TYPE_OPTION;

                    $arg = substr($arg, 2);

                } elseif (starts_with($arg, '-')) {

                    $type = Argument::TYPE_FLAG;

                    $arg = substr($arg, 1);

                } else {

                    $type = Argument::TYPE_ARGUMENT;

                }

                // IsArray
                // ====================================================================

                $is_array = false;

                if (ends_with($arg, '*')) {

                    $is_array = true;

                    $arg = substr($arg, 0, strlen($arg) -1);

                }

                // IsRequired Option
                // =====================================================================

                $is_required = false;

                if (ends_with($arg, '!')) {

                    $is_required = ($type === Argument::TYPE_OPTION) ? true : false;

                    $arg = substr($arg, 0, strlen($arg) -1);

                }

                // Name and Option Default Value
                // ======================================================================

                if (strpos($arg, '=')) {

                    [$name, $default] = explode('=', $arg);

                    if ($type !== Argument::TYPE_OPTION) $default = null;

                } else {

                    $name = $arg;

                    $default = null;

                }

                // Add argument object
                // ======================================================================

                $myArgument = $this->Add($name, $type)

                                    ->setIsArray($is_array)

                                    ->setDescription($description);


                if ($myArgument->Type() === Argument::TYPE_OPTION)

                    $myArgument->setDefault($default)->setIsRequired($is_required);


            }

        }


    }
}
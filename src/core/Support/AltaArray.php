<?php
/*
*-----------------------------------------------------------------------------------------------
* AltaArray - SEMIORBIT ARRAY OBJECT CASE INSENSITIVE  			 semiorbit.com
*------------------------------------------------------------------------------------------------
*
* REF. This class was built on code published by @author Yousef Ismail <cliprz@gmail.com>
* http://docs.php.net/manual/en/class.arrayaccess.php#113865
*
* NB. Array string keys will be changed to LOWER CASE by default
*
*/

namespace Semiorbit\Support;




class AltaArray implements \ArrayAccess, \Countable, \IteratorAggregate
{

    const KEY_CASE = AltaArrayKeys::CI_LOWER;


    private $_Data = array();

    private $_CI_Map = array();

    protected $_KeysCase = AltaArrayKeys::CI_LOWER;

    protected $_CI_Keys = true;

    /**
     * AltaArray constructor.
     * @param $array
     * @param $key_case AltaArrayKeys::[CI_LOWER|CI_UPPER|CASE_SENSITIVE]
     */
    function __construct($array, $key_case = null)
    {

        $this->_KeysCase = $key_case ?: static::KEY_CASE;

        $this->_CI_Keys = $this->_KeysCase != AltaArrayKeys::CASE_SENSITIVE;

        $arr = array();

        $ref_class = new \ReflectionClass($this);

        foreach ($ref_class->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {

            $__cur__lbl = $property->name;

            $arr[$__cur__lbl] = $this->$__cur__lbl;

            unset($this->$__cur__lbl);

        }

        if (empty($arr)) $this->UseArray($array);

        else $this->Merge($arr, $array);


    }

    public function UseArray($array)
    {

        if (is_array($array)) $this->_Data = $array;

        elseif ($array instanceof AltaArray) $this->_Data = $array->ToArray();

        else return false;


        if ($this->_CI_Keys) $this->_CI_Map = $this->MapKeys( $this->_Data );


        return true;

    }


    /**
     * Return array with (lower|upper) case keys and original keys as values
     *
     * @param $array array to map
     * @return array
     */

    protected function MapKeys($array)
    {
        $map = array();

        foreach ($array as $k => $v) $map[$this->setCase($k)] = $k;

        return $map;
    }

    /**
     * Helper function to set array elements
     *
     * @param $key
     * @param $value
     */

    protected function UpdateData($key, $value)
    {

        if (!$this->_CI_Keys) $this->_Data[$key] = $value;

        else {

            $ci_key = $this->setCase($key);

            isset ($this->_CI_Map[$ci_key]) ? $key = $this->_CI_Map[$ci_key] : $this->_CI_Map[$ci_key] = $key;

            $this->_Data[$key] = $value;
        }

    }


    protected function UnsetData($key)
    {

        if ( ! $this->_CI_Keys ) {

            $orig_key = $ci_key = $key;

        } else {

            $ci_key = $this->setCase($key);

            $orig_key = $this->_CI_Map[$this->setCase($key)] ?? $key;

        }

        unset( $this->_CI_Map[ $ci_key ] );

        unset( $this->_Data[ $orig_key ] );

    }

	/**
	 * Get a data by offset
	 *
	 * @param string $offset to retrieve
	 * @access public
	 * @return mixed
	 */

	public function &__get ($key)
    {
        return  $this->_Data[  $this->_CI_Keys ?  $this->_CI_Map[ $this->setCase( $key ) ] : $key ];
	}


	/**
	 * Assigns a value to the specified data
	 *
	 * @param string $key to assign the value to
	 * @param mixed  $value to set
	 * @access public
	 */

	public function __set($key, $value)
    {
        $this->UpdateData($key, $value);
	}


	/**
	 * Check if data exists by offset
	 *
	 * @param string $offset to check for
	 * @access public
	 * @return boolean
	 * @abstracting ArrayAccess
	 */

	public function __isset ($key)
    {
        return isset($this->_Data[$key]) ?: isset($this->_CI_Map[$this->setCase($key)]);
	}


	/**
	 * Unset data by offset
	 *
	 * @param string $offset to unset
	 * @access public
	 */

	public function __unset($key)
    {
        $this->UnsetData($key);
	}


	/**
	 * Assigns a value to the specified offset
	 *
	 * @param string $offset offset to assign the value to
	 * @param mixed  $value to set
	 * @access public
	 * @abstracting ArrayAccess
	 */

	public function offsetSet($offset, mixed $value): void
    {

		if (is_null($offset)) {

			$this->_Data[] = $value;

		} else {

			$this->UpdateData($offset, $value);

		}

	}


	/**
	 * Check if an offset exists
	 *
	 * @param string $key offset to check for
	 * @access public
	 * @return boolean
	 * @abstracting ArrayAccess
	 */

	public function offsetExists($key): bool
    {
        return isset($this->_Data[$key]) || isset($this->_CI_Map[$this->setCase($key)]);
	}

	/**
	 * Unsets an offset
	 *
	 * @param string $key offset to unset
	 * @access public
	 * @abstracting ArrayAccess
	 */

	public function offsetUnset($offset): void
    {
		$this->UnsetData($offset);
	}


	/**
	 * Returns the value at specified offset
	 *
	 * @param string $key offset to retrieve
	 * @access public
	 * @return mixed
	 * @abstracting ArrayAccess
	 */

	public function &offsetGet($offset): mixed
    {
        return  $this->_Data[  $this->_CI_Keys ?  $this->_CI_Map[ $this->setCase( $offset ) ] : $offset ];
	}

    /**
     * An instance of the object implementing Iterator or Traversable
     *
     * @access public
     * @return \ArrayIterator
     * @abstracting IteratorAggregate
     */

	public function getIterator(): \ArrayIterator
    {
		return new \ArrayIterator($this->_Data);
	}



	/**
	 * Count all elements
	 *
	 * @access public
	 * @return int
	 * @abstracting Countable
	 */

	public function count(): int
    {
        return count( $this->_Data );
	}


	/**
	 * Returns Array
	 *
	 * @access public
	 * @return array
	 */

	public function ToArray(): array
    {
		return $this->_Data;
	}

	/**
	 * Merge multiple arrays with data
	 *
	 * @param array $array1 _List of arrays or AltaArray(s) to merge. One array at least
     * @param array|null $_ Optional more arrays to merge.
	 * @access pubic
	 * @return array
	 */

	public function Merge(array $array1, $_ = null): array
    {

		$arg_list = func_get_args();


		foreach ($arg_list as $k => $arr)
		{
			if ( ! is_array( $arr ) )

                if ( $arr instanceof AltaArray )  $arg_list[ $k ] = $arr->ToArray(); else unset( $arg_list[ $k ] );

            if ( $arg_list[ $k ] ) foreach($arg_list[$k] as $mk => $val)  $this->UpdateData($mk, $val);

		}

		return $this->_Data;

	}


	private function setCase($key)
	{

		if ( ! $this->_CI_Keys ) return $key;

		elseif ( $this->_KeysCase == AltaArrayKeys::CI_LOWER ) return strtolower($key);

		else return strtoupper($key);

	}




}

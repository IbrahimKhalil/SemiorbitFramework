<?php

namespace Semiorbit\Http;

use InvalidArgumentException;
use Semiorbit\Field\Field;
use Semiorbit\Support\FilterInput;


/**
 * ---------------------------------------------------------------------------------------------
 * Read GET inputs and assign filtered values to a bound field.
 *
 * Instance methods check whether the requested input exists before filtering and assigning it.
 * When the input is absent, the configured missing-input policy determines the field's value.
 *
 * Has() and ValueOf() are static utilities and do not require a field or an instance.
 * These utilities do not apply missing-input policies or modify any field.
 */
class RequestInput
{

    /** Leave the field's current value unchanged when the input is absent. */
    public const KEEP_FIELD_VALUE = 0;

    /** Restore the field's configured DefaultValue when the input is absent. */
    public const SET_FIELD_DEFAULT_VALUE = 1;

    /** Assign the constructor's alternative value when the input is absent. */
    public const SET_ALT_VALUE = 2;

    /** Assign null when the input is absent. */
    public const EMPTY_VALUE = 3;


    protected Field $_Field;

    protected int $_OnMissingInput;

    protected mixed $_AltValue;



    /**
     * ---------------------------------------------------------------------------------------------
     * Bind a field and configure how assignment methods handle missing inputs.
     *
     * Missing-input policies apply only when the requested key does not exist.
     * A present input is passed to its selected filter, including an empty or null value.
     *
     * Default and alternative values are assigned directly without request filtering.
     *
     * @param Field $field The field receiving filtered input values.
     * @param int $on_missing_input One of this class's missing-input policy constants.
     * @param mixed $alt_value The value assigned when SET_ALT_VALUE is selected.
     */

    public function __construct(Field $field, int $on_missing_input = RequestInput::KEEP_FIELD_VALUE, mixed $alt_value = null)
    {

        if ($on_missing_input < self::KEEP_FIELD_VALUE || $on_missing_input > self::EMPTY_VALUE) $on_missing_input = self::KEEP_FIELD_VALUE;


        $this->_Field = $field;

        $this->_OnMissingInput = $on_missing_input;

        $this->_AltValue = $alt_value;

    }



    /**
     * ---------------------------------------------------------------------------------------------
     * Check whether a GET input key exists, regardless of its value.
     *
     * An existing key returns true even when its value is null, false, zero, or an empty string.
     * This method checks presence only and does not filter or validate the value.
     *
     * @param int|string $var The input key to check.
     *
     * @return bool True when the key exists in $_GET; otherwise false.
     */

    public static function Has(int|string $var): bool
    {
        return array_key_exists($var, $_GET);
    }



    /**
     * ---------------------------------------------------------------------------------------------
     * Get the raw GET input value without filtering or modifying a field.
     *
     * An absent key and a key containing null both return null.
     * Use Has() when the distinction between those cases is required.
     *
     * @param int|string $var The input key to read.
     *
     * @return mixed The raw input value, or null when the key is absent.
     */

    public static function ValueOf(int|string $var): mixed
    {
        return $_GET[$var] ?? null;
    }


    /**
     * ---------------------------------------------------------------------------------------------
     * Filter a GET input as a string and assign the result to the bound field.
     *
     * Filtering follows FilterInput::String(). When the input key is absent,
     * the configured missing-input policy is applied instead.
     *
     * @param string|int $var The input key to read.
     *
     * @return Field The bound field after assignment or missing-input handling.
     */

    public function String(string|int $var): Field
    {

        return static::Has($var)

            ? $this->_Field->setValue(FilterInput::String($var))

            : $this->OnMissingInput();

    }



    /**
     * ---------------------------------------------------------------------------------------------
     * Filter a GET input as a date and assign the formatted result to the bound field.
     *
     * Filtering follows FilterInput::Date(). When the input key is absent,
     * the configured missing-input policy is applied instead.
     *
     * @param string|int $var The input key to read.
     * @param string $format The output date format accepted by the existing filter.
     *
     * @return Field The bound field after assignment or missing-input handling.
     */

    public function Date(string|int $var, string $format = 'Y-m-d'): Field
    {

        return static::Has($var)

            ? $this->_Field->setValue(FilterInput::Date($var, $format))

            : $this->OnMissingInput();

    }


    /**
     * ---------------------------------------------------------------------------------------------
     * Filter a GET input as a date and time and assign the result to the bound field.
     *
     * Filtering follows FilterInput::DateTime(). When the input key is absent,
     * the configured missing-input policy is applied instead.
     *
     * @param string|int $var The input key to read.
     * @param string $format The output date and time format accepted by the existing filter.
     *
     * @return Field The bound field after assignment or missing-input handling.
     */

    public function DateTime(string|int $var, string $format = 'Y-m-d H:i:s'): Field
    {

        return static::Has($var)

            ? $this->_Field->setValue(FilterInput::DateTime($var, $format))

            : $this->OnMissingInput();

    }


    /**
     * ---------------------------------------------------------------------------------------------
     * Filter a GET input as an integer and assign the result to the bound field.
     *
     * Filtering follows FilterInput::Number(). When the input key is absent,
     * the configured missing-input policy is applied instead.
     *
     * @param string|int $var The input key to read.
     *
     * @return Field The bound field after assignment or missing-input handling.
     */

    public function Number(string|int $var): Field
    {

        return static::Has($var)

            ? $this->_Field->setValue(FilterInput::Number($var))

            : $this->OnMissingInput();

    }


    /**
     * ---------------------------------------------------------------------------------------------
     * Filter a GET input as a float and assign the result to the bound field.
     *
     * Filtering follows FilterInput::NumberFloat(). When the input key is absent,
     * the configured missing-input policy is applied instead.
     *
     * @param string|int $var The input key to read.
     *
     * @return Field The bound field after assignment or missing-input handling.
     */

    public function NumberFloat(string|int $var): Field
    {

        return static::Has($var)

            ? $this->_Field->setValue(FilterInput::NumberFloat($var))

            : $this->OnMissingInput();

    }


    /**
     * ---------------------------------------------------------------------------------------------
     * Filter a GET input as a boolean and assign the result to the bound field.
     *
     * Filtering follows FilterInput::Boolean(). A present false value is processed normally.
     * When the input key is absent, the configured missing-input policy is applied instead.
     *
     * @param string|int $var The input key to read.
     *
     * @return Field The bound field after assignment or missing-input handling.
     */

    public function Boolean(string|int $var): Field
    {

        return static::Has($var)

            ? $this->_Field->setValue(FilterInput::Boolean($var))

            : $this->OnMissingInput();

    }


    /**
     * ---------------------------------------------------------------------------------------------
     * Filter a GET input as a hexadecimal string and assign the result to the bound field.
     *
     * Filtering follows FilterInput::Hex(). When the input key is absent,
     * the configured missing-input policy is applied instead.
     *
     * @param string|int $var The input key to read.
     *
     * @return Field The bound field after assignment or missing-input handling.
     */

    public function Hex(string|int $var): Field
    {

        return static::Has($var)

            ? $this->_Field->setValue(FilterInput::Hex($var))

            : $this->OnMissingInput();

    }


    /**
     * ---------------------------------------------------------------------------------------------
     * Filter a GET input as an email address and assign the result to the bound field.
     *
     * Filtering follows FilterInput::Email(). When the input key is absent,
     * the configured missing-input policy is applied instead.
     *
     * @param string|int $var The input key to read.
     *
     * @return Field The bound field after assignment or missing-input handling.
     */

    public function Email(string|int $var): Field
    {

        return static::Has($var)

            ? $this->_Field->setValue(FilterInput::Email($var))

            : $this->OnMissingInput();

    }


    /**
     * ---------------------------------------------------------------------------------------------
     * Filter a GET input as a telephone number and assign the result to the bound field.
     *
     * Filtering and country-code handling follow FilterInput::Tel().
     * When the input key is absent, the configured missing-input policy is applied instead.
     *
     * @param string|int $var The input key to read.
     * @param bool $force_leading_plus Whether the input must already have a leading plus sign.
     * @param int|string|null $default_country_code Country code used by the filter when applicable.
     *
     * @return Field The bound field after assignment or missing-input handling.
     */

    public function Tel(string|int $var, bool $force_leading_plus = false, int|string|null $default_country_code = null): Field
    {

        return static::Has($var)

            ? $this->_Field->setValue(FilterInput::Tel($var, $force_leading_plus, $default_country_code))

            : $this->OnMissingInput();

    }



    /**
     * ---------------------------------------------------------------------------------------------
     * Apply the configured missing-input policy to the bound field.
     *
     * KEEP_FIELD_VALUE preserves the current value.
     * SET_FIELD_DEFAULT_VALUE restores the field's DefaultValue through ResetToDefault().
     * SET_ALT_VALUE assigns the constructor's alternative value.
     * EMPTY_VALUE assigns null.
     *
     * @return Field The bound field after the selected policy has been applied.
     */

    protected function OnMissingInput(): Field
    {

        return match ($this->_OnMissingInput) {

            self::KEEP_FIELD_VALUE => $this->_Field,

            self::SET_FIELD_DEFAULT_VALUE => $this->_Field->ResetToDefault(),

            self::SET_ALT_VALUE => $this->_Field->setValue($this->_AltValue),

            self::EMPTY_VALUE => $this->_Field->setValue(null)

        };

    }

}
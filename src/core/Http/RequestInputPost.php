<?php

namespace Semiorbit\Http;

use Semiorbit\Field\Field;
use Semiorbit\Support\FilterInputPost;



/**
 * ---------------------------------------------------------------------------------------------
 * Read POST inputs and assign filtered values to a bound field.
 *
 * Inherits the field binding, constructor, constants, and missing-input policies.
 * Each assignment method calls its corresponding FilterInputPost method directly.
 *
 * Has() and ValueOf() are static utilities that read POST inputs without requiring an instance.
 */

class RequestInputPost extends RequestInput
{



    /**
     * ---------------------------------------------------------------------------------------------
     * Check whether a POST input key exists, regardless of its value.
     *
     * An existing key returns true even when its value is null, false, zero, or an empty string.
     * This method checks presence only and does not filter or validate the value.
     *
     * @param string|int $var The input key to check.
     *
     * @return bool True when the key exists in $_POST; otherwise false.
     */

    public static function Has($var): bool
    {
        return array_key_exists($var, $_POST);
    }



    /**
     * ---------------------------------------------------------------------------------------------
     * Get the raw POST input value without filtering or modifying a field.
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
        return $_POST[$var] ?? null;
    }



    /**
     * ---------------------------------------------------------------------------------------------
     * Filter a POST input as a string and assign the result to the bound field.
     *
     * Filtering follows FilterInputPost::String(). When the input key is absent,
     * the configured missing-input policy is applied instead.
     *
     * @param int|string $var The input key to read.
     *
     * @return Field The bound field after assignment or missing-input handling.
     */

    public function String(int|string $var): Field
    {

        return static::Has($var)

            ? $this->_Field->setValue(FilterInputPost::String($var))

            : $this->OnMissingInput();

    }



    /**
     * ---------------------------------------------------------------------------------------------
     * Filter a POST input as a date and assign the formatted result to the bound field.
     *
     * Filtering follows FilterInputPost::Date(). When the input key is absent,
     * the configured missing-input policy is applied instead.
     *
     * @param int|string $var The input key to read.
     * @param string $format The output date format accepted by the existing filter.
     *
     * @return Field The bound field after assignment or missing-input handling.
     */

    public function Date(int|string $var, string $format = 'Y-m-d'): Field
    {

        return static::Has($var)

            ? $this->_Field->setValue(FilterInputPost::Date($var, $format))

            : $this->OnMissingInput();

    }



    /**
     * ---------------------------------------------------------------------------------------------
     * Filter a POST input as a date and time and assign the result to the bound field.
     *
     * Filtering follows FilterInputPost::DateTime(). When the input key is absent,
     * the configured missing-input policy is applied instead.
     *
     * @param int|string $var The input key to read.
     * @param string $format The output date and time format accepted by the existing filter.
     *
     * @return Field The bound field after assignment or missing-input handling.
     */

    public function DateTime(int|string $var, string $format = 'Y-m-d H:i:s'): Field
    {

        return static::Has($var)

            ? $this->_Field->setValue(FilterInputPost::DateTime($var, $format))

            : $this->OnMissingInput();

    }



    /**
     * ---------------------------------------------------------------------------------------------
     * Filter a POST input as an integer and assign the result to the bound field.
     *
     * Filtering follows FilterInputPost::Number(). When the input key is absent,
     * the configured missing-input policy is applied instead.
     *
     * @param int|string $var The input key to read.
     *
     * @return Field The bound field after assignment or missing-input handling.
     */

    public function Number(int|string $var): Field
    {

        return static::Has($var)

            ? $this->_Field->setValue(FilterInputPost::Number($var))

            : $this->OnMissingInput();

    }



    /**
     * ---------------------------------------------------------------------------------------------
     * Filter a POST input as a float and assign the result to the bound field.
     *
     * Filtering follows FilterInputPost::NumberFloat(). When the input key is absent,
     * the configured missing-input policy is applied instead.
     *
     * @param int|string $var The input key to read.
     *
     * @return Field The bound field after assignment or missing-input handling.
     */

    public function NumberFloat(int|string $var): Field
    {

        return static::Has($var)

            ? $this->_Field->setValue(FilterInputPost::NumberFloat($var))

            : $this->OnMissingInput();

    }



    /**
     * ---------------------------------------------------------------------------------------------
     * Filter a POST input as a boolean and assign the result to the bound field.
     *
     * Filtering follows FilterInputPost::Boolean(). A present false value is processed normally.
     * When the input key is absent, the configured missing-input policy is applied instead.
     *
     * @param int|string $var The input key to read.
     *
     * @return Field The bound field after assignment or missing-input handling.
     */

    public function Boolean(int|string $var): Field
    {

        return static::Has($var)

            ? $this->_Field->setValue(FilterInputPost::Boolean($var))

            : $this->OnMissingInput();

    }



    /**
     * ---------------------------------------------------------------------------------------------
     * Filter a POST input as a hexadecimal string and assign the result to the bound field.
     *
     * Filtering follows FilterInputPost::Hex(). When the input key is absent,
     * the configured missing-input policy is applied instead.
     *
     * @param int|string $var The input key to read.
     *
     * @return Field The bound field after assignment or missing-input handling.
     */

    public function Hex(int|string $var): Field
    {

        return static::Has($var)

            ? $this->_Field->setValue(FilterInputPost::Hex($var))

            : $this->OnMissingInput();

    }



    /**
     * ---------------------------------------------------------------------------------------------
     * Filter a POST input as an email address and assign the result to the bound field.
     *
     * Filtering follows FilterInputPost::Email(). When the input key is absent,
     * the configured missing-input policy is applied instead.
     *
     * @param int|string $var The input key to read.
     *
     * @return Field The bound field after assignment or missing-input handling.
     */

    public function Email(int|string $var): Field
    {

        return static::Has($var)

            ? $this->_Field->setValue(FilterInputPost::Email($var))

            : $this->OnMissingInput();

    }



    /**
     * ---------------------------------------------------------------------------------------------
     * Filter a POST input as a telephone number and assign the result to the bound field.
     *
     * Filtering and country-code handling follow FilterInputPost::Tel().
     * When the input key is absent, the configured missing-input policy is applied instead.
     *
     * @param int|string $var The input key to read.
     * @param bool $force_leading_plus Whether the input must already have a leading plus sign.
     * @param int|string|null $default_country_code Country code used by the filter when applicable.
     *
     * @return Field The bound field after assignment or missing-input handling.
     */

    public function Tel(int|string $var, bool $force_leading_plus = false, int|string|null $default_country_code = null): Field
    {

        return static::Has($var)

            ? $this->_Field->setValue(FilterInputPost::Tel($var, $force_leading_plus, $default_country_code))

            : $this->OnMissingInput();

    }

}
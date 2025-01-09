<?php

namespace Semiorbit\Support;

class Filter
{

    public static function String($value): ?string
    {
        return $value ? Str::Filter($value) : null;
    }

    public static function Date($value, $format = 'Y-m-d'): ?string
    {
        return $value ? date($format, strtotime($value)) : null;
    }

    public static function DateTime($value, $format = 'Y-m-d H:i:s'): ?string
    {
        return $value ? date($format, strtotime($value)) : null;
    }

    public static function Number($value): ?int
    {
        return ($res = filter_var($value, FILTER_SANITIZE_NUMBER_INT))

        === null || $res === "" || $res === false ? null :  (int) $res;
    }


    public static function NumberFloat($value): ?float
    {
        return ($res = filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION))

        == null || $res === "" || $res === false ? null :  (float) $res;
    }


    public static function Boolean($value): bool
    {
        return Validate::IsTrue(filter_var($value));
    }

    public static function Hex($value): ?string
    {
        /** @noinspection PhpComposerExtensionStubsInspection */
        return ctype_xdigit($value) ? $value : null;
    }


    public static function Numeric($value)
    {
        return filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_SCIENTIFIC) ?: null;
    }

    public static function Decimal($value)
    {
        return filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) ?: null;
    }

    public static  function Email($value): ?string
    {
        return filter_var($value, FILTER_SANITIZE_EMAIL) ?: null;
    }

    public static function Url($value): ?string
    {
        return filter_var($value, FILTER_VALIDATE_URL) ?: null;
    }

    /**
     * Validates and formats/sanitize a phone value.
     *
     * This function sanitizes the input phone value, removes non-numeric characters (except for the `+` sign),
     * and adds a default country code if no `+` is present and `$force_leading_plus` is false.
     * If `$force_leading_plus` is true, the value must start with a `+`.
     *
     * @param string $value The phone value to validate and format.
     * @param bool $force_leading_plus If true, the phone value must start with `+`.
     * @param null $default_country_code The country code to prepend if no leading `+` is present.
     * @return string|null Returns `true` if the phone value is valid, `false` otherwise.
     *
     * Example:
     *
     * | Input            | Description                                      | Result           |
     * |------------------|--------------------------------------------------|------------------|
     * | '0123456789'      | Valid, adds default country code                | Valid (e.g. +1)  |
     * | '+1234567890'     | Valid, already starts with +                     | Valid            |
     * | '9876543210'      | Valid, adds default country code                | Valid (e.g. +1)  |
     * | 'abc123'          | Invalid, contains non-numeric characters         | Invalid          |
     * | '0123456789'      | Invalid when `$force_leading_plus = true`        | Invalid          |
     */

    public static function Tel(mixed $value, bool $force_leading_plus = false, $default_country_code = null): ?string
    {

        // Remove all characters except digits and an optional leading +

        $sanitized = preg_replace('/[^\d+]/', '', $value ?? '');


        // Check if the value matches the general valid format

        if (!preg_match('/^\+?\d+$/', $sanitized)) return null; // Invalid value format


        // Handle the case where a leading + is required

        if ($force_leading_plus && !str_starts_with($sanitized, '+'))

            return null; // Invalid if no leading +



        // Add default country code if provided and no leading +

        if ($default_country_code && !str_starts_with($sanitized, '+')) {

            // Remove leading 0 if it exists

            if (str_starts_with($sanitized, '0')) $sanitized = substr($sanitized, 1);


            // Prepend the default country code

            $sanitized = '+' . $default_country_code . $sanitized;


        }

        return $sanitized;

    }


}
# Request Input

`RequestInput` and `RequestInputPost` read named request inputs, filter their values, and assign the results to a `Field`. They also define what happens when a requested input is absent.

Available in **Semiorbit Framework 8.7.11**.

## Quick start through Field

For a model whose `Lang` property is a field, update its value from a POST input named `lang`:

```php
$myAccount->Lang->setFromRequestInputPost()->String('lang');
```

If `lang` exists, `FilterInputPost::String('lang')` supplies the assigned value. If `lang` is absent, the existing field value remains unchanged.

Use the GET helper for query parameters:

```php
$myAccount->Lang->setFromRequestInput()->String('lang');
```

The input name is explicit. The field property and request key can have different names:

```php
$myAccount->Lang->setFromRequestInputPost()->String('preferred_language');
```

These calls update the field in memory. Application validation and persistence remain part of the normal model/controller flow.

## Classes and responsibilities

| API | Request source | Responsibility |
| --- | --- | --- |
| `Semiorbit\Http\RequestInput` | GET | Bind a field, filter named inputs, and handle missing inputs. |
| `Semiorbit\Http\RequestInputPost` | POST | Provide the same field-assignment behavior for POST inputs. |
| `Field::setFromRequestInput()` | GET | Create a `RequestInput` instance bound to this field. |
| `Field::setFromRequestInputPost()` | POST | Create a `RequestInputPost` instance bound to this field. |
| `Has()` | Selected source | Check key presence without creating an instance. |
| `ValueOf()` | Selected source | Read a raw value without creating an instance. |

`RequestInputPost` extends `RequestInput`. It shares the constructor, field binding, constants, and missing-input handler. Its input methods call `FilterInputPost` directly; the GET methods call `FilterInput` directly.

The implementation uses explicit methods without a dynamic `Apply()` dispatcher or a configurable filter-class selector. Normal framework autoloading handles these classes; application code does not register them.

## Complete account example

This example runs inside an initialized framework application. `ExampleAccount` is an illustrative model with four fields; adapt the property names to the application's own model.

```php
use Semiorbit\Data\Model;
use Semiorbit\Field\Field;



/**
 * ---------------------------------------------------------------------------------------------
 * Define account fields used by the request-input example.
 */

class ExampleAccount extends Model
{



	/**
	 * ---------------------------------------------------------------------------------------------
	 * Initialize the example fields and their default values.
	 */

	public function __construct()
	{

		$this->Lang = Field::Text('lang')->setDefaultValue('en');

		$this->Email = Field::Text('email');

		$this->Mobile = Field::Text('mobile');

		$this->Newsletter = Field::SwitchButton('newsletter')->setDefaultValue(true);

	}

}
```

For this demonstration, populate the existing values explicitly:

```php
$myAccount = new ExampleAccount;

$myAccount->Email->setValue('old@example.com');

$myAccount->Mobile->setValue('+12025550123');
```

Assume an actual form POST contains this URL-encoded body:

```text
lang=ar&email=reader%40example.com&newsletter=0
```

Apply the inputs field by field:

```php
$myAccount->Lang->setFromRequestInputPost()->String('lang');

$myAccount->Email->setFromRequestInputPost()->Email('email');

$myAccount->Mobile->setFromRequestInputPost()->Tel('mobile');

$myAccount->Newsletter->setFromRequestInputPost()->Boolean('newsletter');
```

| Field | Existing value | Request input | Resulting `Value` |
| --- | --- | --- | --- |
| `Lang` | `'en'` | `lang=ar` | `'ar'` |
| `Email` | `'old@example.com'` | `email=reader%40example.com` | `'reader@example.com'` |
| `Mobile` | `'+12025550123'` | Absent | `'+12025550123'` |
| `Newsletter` | `true` | `newsletter=0` | `false` |

Each call handles one field independently. Omitted inputs preserve their fields by default, which is useful when updating only part of a record. A supplied false-like value still reaches the selected filter.

## Direct construction

The Field helpers are convenient factories. Direct construction provides the same behavior:

```php
use Semiorbit\Http\RequestInput;
use Semiorbit\Http\RequestInputPost;


$getInput = new RequestInput($myAccount->Lang);

$getInput->String('lang');

$postInput = new RequestInputPost($myAccount->Email);

$postInput->Email('email');
```

Both classes use this constructor contract:

```php
public function __construct(
	Field $field,

	int $on_missing_input = RequestInput::KEEP_FIELD_VALUE,

	mixed $alt_value = null
)
```

| Parameter | Meaning |
| --- | --- |
| `$field` | The existing Field object receiving assignments. |
| `$on_missing_input` | Policy applied when the requested input key is absent. Defaults to `KEEP_FIELD_VALUE`. |
| `$alt_value` | Value used by `SET_ALT_VALUE`. Defaults to `null`; ignored by the other policies. |

Constructing a reader does not assign anything. Assignment occurs when a filtering method such as `String()` or `Email()` is called.

## Missing-input policies

| Constant | Value | Behavior when the key is absent |
| --- | --- | --- |
| `RequestInput::KEEP_FIELD_VALUE` | `0` | Leave the current field value untouched. |
| `RequestInput::SET_FIELD_DEFAULT_VALUE` | `1` | Call the field's `ResetToDefault()` method to restore `DefaultValue`. |
| `RequestInput::SET_ALT_VALUE` | `2` | Assign the constructor/helper's `$alt_value`. |
| `RequestInput::EMPTY_VALUE` | `3` | Assign `null`. |

The constants are also inherited by `RequestInputPost`. An integer policy outside the range `0` through `3` falls back to `KEEP_FIELD_VALUE`.

Policies apply to **absent keys only**. A present input always reaches the chosen filter. A filtered `null` result is assigned as `null`; it does not trigger a missing-input policy.

Defaults and alternative values are assigned directly without passing through the input filter. `EMPTY_VALUE` explicitly means `null` for every field type.

### Preserve the current value

```php
$myAccount->Lang->setFromRequestInputPost()->String('lang');
```

This is the default behavior. With an existing value of `'ar'` and no `lang` input, the value remains `'ar'`.

### Restore the field default

```php
$myAccount->Lang->setDefaultValue('en');

$myAccount->Lang->setValue('ar');

$myAccount->Lang

	->setFromRequestInputPost(RequestInput::SET_FIELD_DEFAULT_VALUE)

	->String('lang');
```

If `lang` is absent, the field becomes `'en'`. If it is present, the supplied value is filtered and assigned.

### Assign an alternative

```php
$myAccount->Lang

	->setFromRequestInputPost(RequestInput::SET_ALT_VALUE, 'en')

	->String('lang');
```

If `lang` is absent, the field becomes `'en'`, independently of its configured `DefaultValue`.

For a form whose absent checkbox should mean false:

```php
$myAccount->Newsletter

	->setFromRequestInputPost(RequestInput::SET_ALT_VALUE, false)

	->Boolean('newsletter');
```

The default policy preserves an omitted checkbox's previous value. Select an alternative explicitly when the form's semantics require an omitted checkbox to clear it.

### Clear to null

```php
$myAccount->Mobile

	->setFromRequestInputPost(RequestInput::EMPTY_VALUE)

	->Tel('mobile');
```

If `mobile` is absent, the field becomes `null`.

### Policy comparison

Assume the current field value is `'ar'`, its `DefaultValue` is `'en'`, and the alternative is `'fr'`:

| Policy | Missing `lang` | Present `lang=de` passed to `String()` |
| --- | --- | --- |
| `KEEP_FIELD_VALUE` | `'ar'` | `'de'` |
| `SET_FIELD_DEFAULT_VALUE` | `'en'` | `'de'` |
| `SET_ALT_VALUE` | `'fr'` | `'de'` |
| `EMPTY_VALUE` | `null` | `'de'` |

## Static presence checks and raw values

These utilities work without constructing a reader or supplying a Field:

```php
$has_lang = RequestInput::Has('lang');

$raw_lang = RequestInput::ValueOf('lang');

$has_email = RequestInputPost::Has('email');

$raw_email = RequestInputPost::ValueOf('email');
```

`Has()` uses `array_key_exists()` against `$_GET` or `$_POST` and returns a strict `bool`. It checks existence, independently of the value's truthiness.

`ValueOf()` returns the raw value from the selected array, or `null` for an absent key. It does not filter, assign to a field, or apply a missing-input policy.

| State of the selected input array | `Has('value')` | `ValueOf('value')` |
| --- | --- | --- |
| Key absent | `false` | `null` |
| Key exists with `null` | `true` | `null` |
| Key exists with `false` | `true` | `false` |
| Key exists with `0` | `true` | `0` |
| Key exists with `'0'` | `true` | `'0'` |
| Key exists with `''` | `true` | `''` |
| Key exists with an array | `true` | The array |

Ordinary query strings and form submissions generally provide strings or arrays. The null, boolean, and integer cases also describe arrays populated or modified by application code.

To distinguish a missing key from an explicitly supplied null, check presence separately:

```php
if (RequestInputPost::Has('note')) {

	$note = RequestInputPost::ValueOf('note');

}
```

PHP array keys are strings or integers. The input APIs accept named keys such as `'lang'` and numeric keys such as `123`. `ValueOf()` returns `mixed` because an input value can itself be an array. In the current implementation, `RequestInputPost::Has()` leaves its parameter untyped; the remaining public input-key parameters declare the `int|string` union.

`Has()` and `ValueOf()` are static utilities. Calling `ValueOf()` through a reader does not perform raw assignment to its bound field. If raw assignment is required, perform it explicitly and handle presence as appropriate:

```php
if (RequestInputPost::Has('note')) {

	$myAccount->Note->setValue(RequestInputPost::ValueOf('note'));

}
```

Here `Note` represents an additional field on the application's account model.

## Filtering methods

Both reader classes expose the following instance methods. Every method returns the **bound Field**, including when its value is preserved by a missing-input policy.

| Method | Additional arguments | Assigned filter result |
| --- | --- | --- |
| `String($var)` | None | Filtered string or `null`. |
| `Date($var, $format = 'Y-m-d')` | String output format. | Formatted date string or `null`. |
| `DateTime($var, $format = 'Y-m-d H:i:s')` | String output format. | Formatted date/time string or `null`. |
| `Number($var)` | None | Sanitized integer or `null`. |
| `NumberFloat($var)` | None | Sanitized float or `null`. |
| `Boolean($var)` | None | Boolean. |
| `Hex($var)` | None | Hexadecimal string or `null`. |
| `Email($var)` | None | Sanitized email string or `null`. |
| `Tel($var, $force_leading_plus = false, $default_country_code = null)` | Boolean plus-sign requirement; integer/string/null country code. | Sanitized/formatted telephone string or `null`. |

The request key parameter `$var` is typed `string|int` (`int|string` is equivalent). The date format parameters are typed `string`. Telephone options are typed `bool` and `int|string|null` respectively.

Examples below assume the corresponding fields already exist on `$record`:

```php
$record->BirthDate->setFromRequestInputPost()->Date('birth_date');

$record->LastSeen->setFromRequestInputPost()->DateTime('last_seen', 'Y-m-d H:i:s');

$record->Quantity->setFromRequestInputPost()->Number('quantity');

$record->Amount->setFromRequestInputPost()->NumberFloat('amount');

$record->Enabled->setFromRequestInputPost()->Boolean('enabled');

$record->Reference->setFromRequestInputPost()->Hex('reference');

$record->Email->setFromRequestInputPost()->Email('email');

$record->Mobile->setFromRequestInputPost()->Tel('mobile', false, '1');
```

The telephone example supplies a default country code when the existing filter considers it applicable. Passing `true` as `$force_leading_plus` requires the sanitized input to start with `+`.

### Return values and chaining

```php
$input = $myAccount->Lang->setFromRequestInputPost();

$field = $input->String('lang');

$value = $field->Value;
```

`$input` is a `RequestInputPost`; `$field` is the original `$myAccount->Lang` Field. The helper creates a reader, and `String()` returns the bound field after handling that input.

Field methods can therefore follow a filtering method:

```php
$myAccount->Lang

	->setFromRequestInputPost()

	->String('lang')

	->SerializeValueAsString();
```

To handle another model field, start a separate call on that field.

## Existing filter behavior

These readers preserve the behavior of `FilterInput` and `FilterInputPost`. They add field assignment and missing-input policies; they do not redefine sanitization or validation rules.

In the current implementation:

- `String()` converts false-like values, including `''` and `'0'`, to `null` before string filtering. Presence still returns true, so that `null` is assigned.
- `Number()` preserves a sanitized zero as integer `0`. `NumberFloat()` uses a loose null comparison and can convert a sanitized `'0'` to `null`.
- `Boolean()` follows the existing boolean-token handling, including true-like tokens such as `1`, `true`, `yes`, `on`, and `y`.
- `Number()` and `NumberFloat()` sanitize input; `Email()` sanitizes an email string. Successful assignment alone does not establish that the original input meets all application validation rules.
- `DateTime()` performs the existing filter's additional `urldecode()` step before date/time formatting. Dates use the existing parsing behavior and PHP's configured timezone where applicable.
- `ValueOf()` can return arrays. The scalar filtering methods do not add a general array-shape check; choose the appropriate input handling for the expected request shape.

### Request arrays and PHP input snapshots

`Has()` and `ValueOf()` read the current `$_GET` or `$_POST` array. Several existing filters read values through that array, but `Number()`, `NumberFloat()`, and `Boolean()` use PHP's `filter_input()` instead.

As a result, assigning values to `$_POST` or `$_GET` in PHP is not a reliable simulation of an actual numeric or boolean request input. Use real HTTP query/form inputs when exercising those filters. The readers do not merge GET and POST sources or decode a raw JSON request body.

## Field helper reference

| Field method | Parameters | Return type |
| --- | --- | --- |
| `setFromRequestInput()` | `int $on_missing_input = RequestInput::KEEP_FIELD_VALUE`, `mixed $alt_value = null` | `RequestInput` |
| `setFromRequestInputPost()` | `int $on_missing_input = RequestInput::KEEP_FIELD_VALUE`, `mixed $alt_value = null` | `RequestInputPost` |

Use lowercase `set` in these method names, following Semiorbit Framework conventions. The filtering methods retain their declared names, such as `String()`, `Email()`, and `Boolean()`.

## Source reference

- [GET reader: RequestInput](../../src/core/Http/RequestInput.php)
- [POST reader: RequestInputPost](../../src/core/Http/RequestInputPost.php)
- [Field integration](../../src/core/Field/Field.php)
- [Existing GET filters](../../src/core/Support/FilterInput.php)
- [Existing POST filters](../../src/core/Support/FilterInputPost.php)
- [Data serialization](DATA_SERIALIZATION.md)

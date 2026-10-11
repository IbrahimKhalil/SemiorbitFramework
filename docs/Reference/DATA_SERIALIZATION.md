# Data Serialization

## Complete row example

The model below exports a populated row with these values:

| Field | Raw `Value` | Serialization |
| --- | --- | --- |
| `id` | `'92395783831158784'` | String; BIGINT UNSIGNED UUID_SHORT ID |
| `name` | `'John Doe'` | String |
| `enabled` | `'1'` | Bool |
| `quantity` | `'3'` | Int |
| `amount` | `'19.75'` | Decimal |
| `note` | `null` | Nullable string |
| `status` | `'2'` | Int with display text `'Approved'` |
| `reference` | `' so-123 '` | Custom conversion and custom output |

~~~php
$row = new ExampleOrder;

$row->Id->Value = '92395783831158784';

$row->Name->Value = 'John Doe';

$row->Enabled->Value = '1';

$row->Quantity->Value = '3';

$row->Amount->Value = '19.75';

$row->Note->Value = null;

$row->Status->Value = '2';

$row->Reference->Value = ' so-123 ';

$data = $row->SerializeData();

$json = $row->ToJson(JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
~~~

`$data` is an associative PHP array. `$json` is the following JSON text:

~~~json
{
  "id": "92395783831158784",
  "name": "John Doe",
  "enabled": true,
  "quantity": 3,
  "amount": 19.75,
  "note": null,
  "status": {
    "value": 2,
    "text": "Approved"
  },
  "reference": {
    "code": "SO-123",
    "length": 6
  }
}
~~~

### Model definition

This example defines an ID, text fields, a Boolean switch, an integer number, a decimal amount, a text area, and a select field. It runs inside an initialized framework application with its autoloader and configuration. The values above are assigned explicitly, so running this example performs no database operations.

~~~php
use Semiorbit\Data\Model;
use Semiorbit\Field\DataField;
use Semiorbit\Field\DataType;
use Semiorbit\Field\Field;



/**
 * Example order with typed values and custom exported data.
 */

class ExampleOrder extends Model
{

	public function __construct()
	{

		$this->Id = Field::ID('id')

			->IsUUID_SHORT()

			->SerializeValueAsString();

		## _____________________________________________________________________________________________________________

		$this->Name = Field::Text('name');

		$this->Enabled = Field::SwitchButton('enabled')->SerializeValueAsBool();

		$this->Quantity = Field::Number('quantity')->SerializeValueAsInt();

		$this->Amount = DataField::Decimal('amount')->SerializeValueAsDecimal();

		$this->Note = Field::TextArea('note');

		## _____________________________________________________________________________________________________________

		$this->Status = Field::Select('status')

			->setOptions([

				1 => 'Pending',

				2 => 'Approved'

			])

			->setType(DataType::INT)

			->SerializeValueAsInt()

			->SerializeValueAndText();

		## _____________________________________________________________________________________________________________

		$this->Reference = Field::Text('reference')

			->UseCastingCallback(static function ($value): string
			{
				return strtoupper(trim(strval($value)));
			})

			->UseDataSerializer(static function (Field $field): array
			{

				$code = $field->CastValue();

				return [

					'code' => $code,

					'length' => strlen($code)

				];

			});

		## _____________________________________________________________________________________________________________

	}

}
~~~

The model property keys, such as `Id` and `Enabled`, identify the fields inside the model. Exported keys come from each field's `Name`, such as `id` and `enabled`.

`Status` resolves its display label from select options; the example shows the English labels. `Reference` converts the raw value once inside its serializer, then builds a custom array from that result.

| Property | Field definition | Stored data / control | Exported data |
| --- | --- | --- | --- |
| `Id` | `Field::ID()->IsUUID_SHORT()` | MySQL-generated ID; no input control | String |
| `Name` | `Field::Text()` | Text input / VARCHAR | String |
| `Enabled` | `Field::SwitchButton()` | Boolean switch / BOOL | Bool |
| `Quantity` | `Field::Number()` | Number input / INT | Int |
| `Amount` | `DataField::Decimal()` | Decimal number input / DECIMAL | Float |
| `Note` | `Field::TextArea()` | Text area / TEXT | String or null |
| `Status` | `Field::Select()->setType(DataType::INT)` | Select options / INT | Value-and-text array |
| `Reference` | `Field::Text()` with custom callbacks | Text input / VARCHAR | Custom array |

### BIGINT UUID_SHORT IDs

A motivating use case for string serialization is a MySQL `UUID_SHORT()` identifier stored in a `BIGINT UNSIGNED` column. The ID field keeps its integer storage configuration; `SerializeValueAsString()` controls its exported representation.

`IsUUID_SHORT()` selects the database ID generator and configures an unsigned integer ID with a maximum length of 20 digits. The SQL column must be declared `BIGINT UNSIGNED` to store the full identifier range. This helper uses the framework's `DataType::INT` category; it does not change an existing SQL column.

The example ID, `92395783831158784`, exceeds JavaScript's largest safe integer, `9007199254740991`. Exporting it as a JSON string keeps all its digits when a JavaScript client parses the response. Keep the ID as a string through frontend state, requests, and comparisons; converting it to a JavaScript Number loses that protection.

Assign the raw value as an exact string, as shown above, or as an exact integer supported by the PHP platform. Do not first convert a large identifier to a float: string serialization cannot recover digits already lost.

`UUID_SHORT()` generation belongs to database persistence, not serialization. Creating the model or calling `SerializeData()` does not generate a new ID. The example uses a fixed sample value to make the output reproducible.

## How serialization works

| Value or method | Responsibility | Result |
| --- | --- | --- |
| `Field::Value` | Holds the original value. | Original PHP value |
| `UseCastingCallback()` | Configures or clears custom conversion of raw Value. | Same Field |
| `SerializeValueAsString()` | Configures string conversion; preserves null by default. | Same Field |
| `SerializeValueAsBool()` | Configures Boolean conversion; preserves null by default. | Same Field |
| `SerializeValueAsInt()` | Configures integer conversion; preserves null by default. | Same Field |
| `SerializeValueAsDecimal()` | Configures float conversion; preserves null by default. | Same Field |
| `CastValue()` | Executes conversion, or returns raw Value. | Converted PHP value |
| `UseDataSerializer()` | Configures or clears the exported-representation callback. | Same Field |
| `SerializeValueAndText()` | Configures a serializer combining CastValue and TextValue. | Same Field |
| `Field::SerializeData()` | Executes the serializer, or falls back to CastValue. | Exported PHP value |
| `Model::SerializeData()` | Collects serialized fields by Name for one row. | Associative PHP array |
| `Model::JsonSerialize()` | Supplies serialized row data to PHP's JSON interface. | Associative PHP array |
| `Model::ToJson()` | Encodes the serialized row data. | JSON string, false on failure, or an exception in exception mode |
| `UseHtmlBuilder()` | Configures the display-output callback. | Same Field |
| `Html()` | Executes the active display builder. | Display output, possibly HTML |
| `TextValue()` | Delegates to Html without stripping markup. | Same display output |
| `ToString()` | Converts raw Value directly with strval. | String |
| `__toString()` / `(string) $field` | Converts Html output to a string. | Display string |

A Field controls its converted value and exported representation. A Model collects the serialized results of its current fields. JSON encoding turns those PHP values into JSON text.

There are two independent callback settings:

- **Casting callback:** receives the raw `Value` and returns a converted value.
- **Data serializer:** receives the Field and returns its exported representation.

Configuration methods install callbacks and return the same Field for chaining. They do not immediately convert `Value` or overwrite it.

Execution follows these rules:

1. `Model::SerializeData()` calls `SerializeData()` on every Field returned by `Fields()`.
2. A Field with a data serializer runs that callback.
3. A Field without a data serializer falls back to `CastValue()`.
4. `CastValue()` runs the casting callback, or returns raw `Value` when none is configured.
5. The Model collects each result under the field's `Name`.
6. `ToJson()`, or PHP's `json_encode()`, encodes the resulting data.

A custom serializer controls whether casting is used. Call `$field->CastValue()` inside it when conversion is wanted; `$field->Value` reads the original value.

Serialization does not validate input, save a row, or prepare SQL values. The configured export type is independent of the field's database type.

## Built-in value conversions

### SerializeValueAsString()

~~~php
public function SerializeValueAsString(bool $nullable = true): static
~~~

Installs a `strval()` conversion. Useful for text and identifiers that should remain strings in JSON.

~~~php
$field = Field::ID('id')

	->IsUUID_SHORT()

	->SerializeValueAsString();

$field->Value = '92395783831158784';

$value = $field->SerializeData(); // '92395783831158784'
~~~

If the raw value is already `'000123'`, the leading zeroes remain. String conversion cannot restore digits or formatting already lost before assignment.

### SerializeValueAsBool()

~~~php
public function SerializeValueAsBool(bool $nullable = true): static
~~~

Installs a `boolval()` conversion.

~~~php
$field = Field::SwitchButton('enabled')->SerializeValueAsBool();

$field->Value = '0';

$value = $field->SerializeData(); // false
~~~

This follows PHP Boolean conversion: `'0'`, `''`, and numeric zero become `false`. The string `'false'` becomes `true` because it is a nonempty string other than `'0'`. Use a custom casting callback when textual Boolean tokens need parsing.

### SerializeValueAsInt()

~~~php
public function SerializeValueAsInt(bool $nullable = true): static
~~~

Installs an `intval()` conversion.

~~~php
$field = Field::Number('quantity')->SerializeValueAsInt();

$field->Value = '12.9';

$value = $field->SerializeData(); // 12
~~~

Decimal values are truncated toward zero. This method does not check whether an input is a valid integer.

### SerializeValueAsDecimal()

~~~php
public function SerializeValueAsDecimal(bool $nullable = true): static
~~~

Installs a `floatval()` conversion and exports a PHP `float`.

~~~php
$field = DataField::Decimal('amount')->SerializeValueAsDecimal();

$field->Value = '19.7500';

$value = $field->SerializeData(); // 19.75
~~~

The result does not preserve fixed decimal scale or exact decimal arithmetic. For an existing amount string that must retain `'19.7500'`, use `SerializeValueAsString()`. Large identifiers should also use strings when their digits must be preserved for JSON consumers.

### Null handling

All four methods preserve explicit `null` by default:

| Method | `Value = null`, default `true` | `Value = null`, `false` |
| --- | --- | --- |
| `SerializeValueAsString()` | `null` | `''` |
| `SerializeValueAsBool()` | `null` | `false` |
| `SerializeValueAsInt()` | `null` | `0` |
| `SerializeValueAsDecimal()` | `null` | `0.0` |

~~~php
$field = Field::Number('quantity');

$field->Value = null;

$field->SerializeValueAsInt();

$nullable = $field->CastValue(); // null

$field->SerializeValueAsInt(false);

$non_nullable = $field->CastValue(); // 0
~~~

`Value` still contains `null`. Passing `false` changes the output conversion only; it does not change the field's required setting, defaults, or database nullability. Empty strings are converted normally rather than treated as null.

## Custom casting and serialization

### UseCastingCallback()

~~~php
public function UseCastingCallback(?callable $callback): static
~~~

Sets a custom converter. The callback receives raw `Value`, not the Field.

~~~php
$field = Field::Text('code');

$field->Value = ' usd ';

$field->UseCastingCallback(static function ($value): ?string
{
	return ($value === null) ? null : strtoupper(trim(strval($value)));
});

$value = $field->CastValue(); // 'USD'
~~~

The callback defines its own null handling and return type. In this example, `?string` permits the returned null. The original `Value` remains `' usd '`.

### CastValue()

~~~php
public function CastValue(): mixed
~~~

Executes the configured casting callback. Without a callback, it returns raw `Value` unchanged. It does not run the data serializer or produce JSON text.

~~~php
$field = Field::Number('quantity')->SerializeValueAsInt();

$field->Value = '3';

$raw = $field->Value; // '3'

$converted = $field->CastValue(); // 3
~~~

### UseDataSerializer()

~~~php
public function UseDataSerializer(?callable $callback): static
~~~

Sets a custom export builder. Its callback receives the Field and can return a scalar, null, an array, or another JSON-compatible value.

~~~php
$field = DataField::Decimal('amount')->SerializeValueAsDecimal();

$field->Value = '19.75';

$field->UseDataSerializer(static function (Field $field): array
{
	return [

		'value' => $field->CastValue(),

		'currency' => 'USD'

	];
});

$data = $field->SerializeData(); // ['value' => 19.75, 'currency' => 'USD']
~~~

The serializer explicitly calls `CastValue()`. Returning `$field->Value` would instead export the original string `'19.75'`.

Return PHP data from the callback. Do not encode it with `json_encode()` unless a JSON string is deliberately part of the output contract.

### Field::SerializeData()

~~~php
public function SerializeData(): mixed
~~~

Executes the custom data serializer when one is configured. Otherwise, it returns `CastValue()`. It exports one Field, not a whole row, and does not encode JSON.

For the amount example above:

~~~php
$value = $field->CastValue(); // 19.75

$data = $field->SerializeData(); // ['value' => 19.75, 'currency' => 'USD']
~~~

### SerializeValueAndText()

~~~php
public function SerializeValueAndText(string $value_key = 'value', string $text_key = 'text'): static
~~~

Installs a serializer that combines `CastValue()` with `TextValue()`. It retains the current casting callback.

Using `$row->Status` from the complete example:

~~~php
$data = $row->Status->SerializeData(); // ['value' => 2, 'text' => 'Approved']

$row->Status->SerializeValueAndText('id', 'label');

$data = $row->Status->SerializeData(); // ['id' => 2, 'label' => 'Approved']
~~~

The outer model key remains `status`. Only the nested keys change. Use different value and text keys; identical keys cause the text entry to overwrite the value entry.

Nullable casting preserves null in the value entry. The text entry is whatever `TextValue()` returns for that Field, including any markup its HTML builder produces.

### Replacing and resetting callbacks

Each Field has one casting callback and one data serializer. A new callback replaces the previous callback in the same slot.

~~~php
$field->SerializeValueAsInt();

$field->SerializeValueAsString(); // Replaces integer conversion.

$field->SerializeValueAndText();

$field->UseDataSerializer(null); // Removes value/text output; casting remains.

$field->UseCastingCallback(null); // Removes casting; raw Value is exported.
~~~

Removing a casting callback does not remove an existing data serializer. Removing a serializer does not remove casting.

Execution uses the current `Value` and configured callbacks each time. Keep callbacks free of unintended mutations. A serializer must not call its own Field's `SerializeData()`; that recursively invokes the same serializer. Use `CastValue()` for its converted value.

## Model and JSON methods

### Model::SerializeData()

~~~php
public function SerializeData(): array
~~~

Returns one associative PHP array for the current row. Every Field returned by `Fields()` contributes `Name => serialized result`.

~~~php
$data = $row->SerializeData();

$status = $data['status']; // ['value' => 2, 'text' => 'Approved']
~~~

This method does not encode JSON or iterate a collection of rows. It adds no separate permission check or export-field filtering; choose the fields or output projection required by the response.

### JsonSerialize()

~~~php
public function JsonSerialize(): array
~~~

Returns the row's serialized PHP array for the `\JsonSerializable` interface.

~~~php
$data = $row->JsonSerialize();

$json = json_encode($row, JSON_UNESCAPED_UNICODE);
~~~

Calling `JsonSerialize()` directly returns data. Calling `json_encode($row)` invokes the hook and returns JSON text. PHP's interface spells the method `jsonSerialize()`; PHP method names are case-insensitive.

The interface controls JSON output. PHP's `serialize()` and `unserialize()` are separate mechanisms.

### ToJson()

~~~php
public function ToJson(int $options = JSON_UNESCAPED_UNICODE, int $depth = 512): false|string
~~~

Encodes the row's serialized data. It defaults to readable Unicode and a maximum nesting depth of 512.

~~~php
$json = $row->ToJson();

$formatted = $row->ToJson(JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

$strict = $row->ToJson(JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
~~~

Pass a positive `$depth` when a different nesting limit is needed. `ToJson()` and direct `json_encode($row, $options, $depth)` produce equivalent data for matching options and depth.

| JSON option | Effect |
| --- | --- |
| `JSON_UNESCAPED_UNICODE` | Keeps Unicode characters readable. |
| `JSON_PRETTY_PRINT` | Adds indentation and line breaks. |
| `JSON_PRESERVE_ZERO_FRACTION` | Encodes a float such as `12.0` with its fractional zero; does not enforce a decimal scale. |
| `JSON_THROW_ON_ERROR` | Throws `\JsonException` for JSON encoding failures instead of returning false. |

Prefer per-field conversions over `JSON_NUMERIC_CHECK` when identifiers or numeric-looking strings must remain strings.

Without exception mode, check for failure strictly:

~~~php
$json = $row->ToJson();

if ($json === false) {

	throw new \RuntimeException(json_last_error_msg());

}
~~~

Invalid UTF-8, resources, recursive data, excessive nesting, and non-finite floats can cause encoding failures. Exceptions from custom callbacks propagate; `ToJson()` does not catch them.

Use `SerializeData()` when a response layer expects PHP data and will encode it. Use `ToJson()` when it expects JSON text. Encoding a `ToJson()` result again creates a JSON string containing escaped JSON.

### Multiple rows

For an iterable of populated models:

~~~php
$data = [];

foreach ($rows as $row) {

	$data[] = $row->SerializeData();

}

$json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
~~~

Sequential outer keys produce a JSON array. A populated row with named field keys produces a JSON object. With default flags, an empty row array or empty collection encodes as `[]`.

## Display text, HTML, and raw strings

These methods provide display output or raw string conversion. They do not automatically apply the data-serialization casting callback.

### UseHtmlBuilder()

~~~php
public function UseHtmlBuilder(callable $html_builder_func)
~~~

Sets the display builder. Its callback receives the Field. Calling this method configures display output; it does not immediately render or configure a data serializer.

~~~php
$field = Field::Text('status')->SerializeValueAsInt();

$field->Value = '2';

$field->UseHtmlBuilder(static function (Field $field): string
{
	return ($field->Value === '2') ? '<strong>Approved</strong>' : 'Pending';
});
~~~

### Html()

~~~php
public function Html()
~~~

Runs the configured HTML builder, or the Field type's default display builder, and returns its output. It does not output the result to the browser by itself.

For the status Field above:

~~~php
$html = $field->Html(); // '<strong>Approved</strong>'
~~~

Default rendering depends on the Field type. For example, a text Field escapes its text by default, while a select Field resolves option labels. A custom builder is responsible for escaping dynamic text that it puts inside markup.

### TextValue()

~~~php
public function TextValue()
~~~

Delegates to `Html()` and returns the same display output. Despite its name, it does not strip HTML.

~~~php
$text = $field->TextValue(); // '<strong>Approved</strong>'

$field->SerializeValueAndText();

$data = $field->SerializeData(); // ['value' => 2, 'text' => '<strong>Approved</strong>']
~~~

Use a builder that returns a plain label when the API's `text` entry should contain plain text.

### ToString()

~~~php
public function ToString()
~~~

Returns `strval(Value)`. It converts the raw value directly, bypassing the HTML builder and serialization callbacks.

~~~php
$raw_text = $field->ToString(); // '2'

$converted = $field->CastValue(); // 2
~~~

A raw null becomes `''` here, even when nullable serialization preserves null.

### __toString()

~~~php
public function __toString()
~~~

PHP string conversion uses `strval(Html())`. This differs from calling `ToString()`:

~~~php
$display = (string) $field; // '<strong>Approved</strong>'

$raw = $field->ToString(); // '2'
~~~

## References

- [JsonSerializable](https://www.php.net/manual/en/class.jsonserializable.php)
- [json_encode](https://www.php.net/manual/en/function.json-encode.php)
- [JSON options](https://www.php.net/manual/en/json.constants.php)
- [strval](https://www.php.net/manual/en/function.strval.php)
- [boolval](https://www.php.net/manual/en/function.boolval.php)
- [intval](https://www.php.net/manual/en/function.intval.php)
- [floatval](https://www.php.net/manual/en/function.floatval.php)
- [Floating-point numbers](https://www.php.net/manual/en/language.types.float.php)

- [MySQL UUID_SHORT()](https://dev.mysql.com/doc/refman/8.4/en/miscellaneous-functions.html#function_uuid-short)
- [JavaScript Number.MAX_SAFE_INTEGER](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Number/MAX_SAFE_INTEGER)

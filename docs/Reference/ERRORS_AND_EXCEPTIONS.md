# Errors and Exceptions

Semiorbit Framework separates the HTTP status, application error code, public message, diagnostic message, and error type. Public messages may be ordinary strings or JSON language maps containing English and the active locale. Exceptions retain the language map while exposing readable English through PHP's native `getMessage()` method.

Location: `docs/Reference/ERRORS_AND_EXCEPTIONS.md`.

Verified against Semiorbit Framework **8.7.5**, public `master` commit [`333bda0a73792252ad8c0b890f4b187bd8bc331a`](https://github.com/IbrahimKhalil/SemiorbitFramework/tree/333bda0a73792252ad8c0b890f4b187bd8bc331a), on **2026-10-14**. The examples assume an initialized application with its normal configuration, package initialization, and autoloading already in place. `AccountsErrors` below is an illustrative package enum, not a built-in framework enum.

## Contents

- [Core concepts and responsibilities](#core-concepts-and-responsibilities)
- [RFC basis and Semiorbit Framework's message contract](#rfc-basis-and-semiorbit-frameworks-message-contract)
- [Complete localized error example](#complete-localized-error-example)
- [Error enums and translation discovery](#error-enums-and-translation-discovery)
- [ErrorMessage reference](#errormessage-reference)
- [Exception classes and methods](#exception-classes-and-methods)
- [Throwing exceptions and aborting execution](#throwing-exceptions-and-aborting-execution)
- [Response processing and HTTP defaults](#response-processing-and-http-defaults)
- [API responses and client handling](#api-responses-and-client-handling)
- [HTML error pages and the copy button](#html-error-pages-and-the-copy-button)
- [Debugging and logging](#debugging-and-logging)
- [Compatibility and implementation boundaries](#compatibility-and-implementation-boundaries)
- [Troubleshooting](#troubleshooting)
- [Source reference](#source-reference)

## Core concepts and responsibilities

| Value | Responsibility | Example |
| --- | --- | --- |
| HTTP status | HTTP meaning of the failure; sent in the response header and recorded in the error body. | `401` |
| Application code | Integer identifying a particular application error. | `1001` |
| Enum case | Source-level name for that code and its translation dictionary key. | `LOGIN_FAILED` |
| Public message | Text suitable for users; plain text or a language map supplied as a string. | `Unable to sign in.` |
| Debug message | Additional diagnostics exposed by the error response only in debug mode. | `Authentication provider rejected the credentials.` |
| Error type | Identifier constructed from host, application metadata, and effective code. | `tag:api.example.com,2026:example/demo-api/example-app/01/1001` |
| Previous exception | PHP exception/throwable retained as the cause of another exception. | `$previous` |

An application code is not automatically an HTTP status. For example, `LOGIN_FAILED = 1001` can be reported with HTTP `401`. The enum supplies the code and message; the caller chooses the HTTP status.

Error enum case names and their translation keys use **UPPER_SNAKE_CASE** across framework, application, and package code. Use `LOGIN_FAILED`, `ACCOUNT_NOT_FOUND`, and `EMAIL_ALREADY_EXISTS`; dictionary keys must match the case names exactly.

| Component | Responsibility |
| --- | --- |
| `ErrorEnumInterface` | Defines `Code(): int` and `Label(): string`. |
| `ErrorEnumTrait` | Implements integer code access, package discovery, translated label generation, and cached English dictionary loading. |
| `ErrorMessage` | Parses and caches a plain message or language map; provides language-specific reads. |
| `AppExceptionTrait` | Retains the message object, HTTP status, and diagnostics on an exception. |
| `AppException` | Application exception extending PHP `Exception`. |
| `AppRuntimeException` | Application exception extending PHP `RuntimeException`. |
| `AppAbortException` | Runtime exception handled as an application-wide abort. |
| `Application` | Provides `Exception()` and `Abort()` throwing helpers. |
| `AppService::Run()` | Catches exceptions in its execution boundary and prepares output; aborts terminate execution. |
| `Response` | Converts exceptions into API error data or an HTML error view. |
| Default error view | Displays localized public text, English support text, the code, and type. |

## RFC basis and Semiorbit Framework's message contract

The design is informed by [RFC 9457 — Problem Details for HTTP APIs](https://datatracker.ietf.org/doc/html/rfc9457), which supersedes RFC 7807. It separates HTTP status, machine-readable problem identity, and human-readable text.

Semiorbit Framework uses its existing `application/json` envelope with `status`, `code`, `msg`, and `type`. It is an **application-specific adaptation**, not the standard `application/problem+json` representation.

| RFC 9457 concept | Semiorbit Framework representation |
| --- | --- |
| `status` | HTTP status in `status`. |
| `type` | Generated identifier in `type`. |
| `title` / `detail` | Public text carried in `msg`; no separate fields. |
| Extension members | Application `code` and debug fields. |
| `instance` | No occurrence identifier generated here. |

RFC 9457 permits localized human-readable strings. Semiorbit Framework's distinction is that `msg` can contain **English and the active locale together**, rather than one negotiated string. This language map is a Semiorbit Framework contract, not an RFC-defined message shape.

The `tag:` identifier follows the scheme described in [RFC 4151 — The 'tag' URI Scheme](https://www.rfc-editor.org/rfc/rfc4151.html). A tag identifies an entity without requiring a retrievable web page. Its authority/date combination and remaining identifier distinguish it. Production authorities should be fully qualified domains controlled at the chosen date; local development values such as `localhost` are not globally unique production authorities.

The localization changes the public text, not the numeric code or the generated type for the same application context. Clients should use codes/types for decisions and messages for display.

## Complete localized error example

This example defines an Accounts package enum, adds English and Arabic dictionaries, and raises a localized error. The sample values `1001`–`1003` are illustrative; choose codes within the application's documented catalog.

### 1. Define the enum

Example file: `src/Accounts/Debug/AccountsErrors.php`.

```php
<?php

namespace Accounts\Debug;

use Semiorbit\Debug\ErrorEnumInterface;
use Semiorbit\Debug\ErrorEnumTrait;



/**
 * Public errors for the Accounts package.
 */

enum AccountsErrors: int implements ErrorEnumInterface
{

	use ErrorEnumTrait;

	case LOGIN_FAILED = 1001;

	case ACCOUNT_NOT_FOUND = 1002;

	case EMAIL_ALREADY_EXISTS = 1003;

}
```

The enum is integer-backed. Each case provides a stable integer value and a source-level name. PHP enum completion and references make it useful in IDEs such as PhpStorm; callers do not need to repeat raw codes or translation keys.

No error-specific registration is required for an existing package. Add the enum under that package's existing namespace and place its dictionaries in the existing `lang` directory. `ErrorEnumTrait` discovers the package and derives the translation source automatically; callers do not add `Register()` calls, namespace indexes, or language-path setters to use an error enum.

For this example, the existing package ID is `accounts`, its namespace is `Accounts`, and its language directory is `src/Accounts/lang/`. The trait can discover an enum in `Accounts\Debug` through that existing package setup. The required additions are the enum, the two dictionaries below, and the call-site use of `Code()` and `Label()`.

### 2. Add the English dictionary

File: `src/Accounts/lang/en/accounts-errors.en.inc`.

```php
<?php

return [

	'LOGIN_FAILED' => 'Unable to sign in.',

	'ACCOUNT_NOT_FOUND' => 'Account not found.',

	'EMAIL_ALREADY_EXISTS' => 'This email address is already registered.'

];
```

### 3. Add the Arabic dictionary

File: `src/Accounts/lang/ar/accounts-errors.ar.inc`.

```php
<?php

return [

	'LOGIN_FAILED' => 'تعذر تسجيل الدخول.',

	'ACCOUNT_NOT_FOUND' => 'الحساب غير موجود.',

	'EMAIL_ALREADY_EXISTS' => 'عنوان البريد الإلكتروني هذا مسجل مسبقًا.'

];
```

Dictionary files return arrays. They are not JSON files and do not print their contents. English and Arabic use the same case-name keys.

### 4. Select a supported language and raise the error

During normal requests, the framework selects a language before controller construction. Arabic must be allowed by `Config::Languages()`; `Lang::UseLang()` falls back to `Config::DefaultLang()` for unsupported values.

For an initialized application example:

```php
use Accounts\Debug\AccountsErrors;
use Semiorbit\Base\Application;
use Semiorbit\Translation\Lang;



Lang::UseLang('ar');

$error = AccountsErrors::LOGIN_FAILED;

Application::Abort(401, $error->Label(), $error->Code());
```

`$error->Code()` returns `1001`. `$error->Label()` returns this **PHP string containing JSON**:

```json
{"ar":"تعذر تسجيل الدخول.","en":"Unable to sign in."}
```

The exception stores the full message through `ErrorMessage`, while its native `getMessage()` returns `Unable to sign in.`. The response reads the retained raw message and, in API mode, inserts the decoded map into the outer response.

Assuming host `api.example.com`, application Composer name `example/demo-api`, `APP_ID=example-app`, `DB_CODE=01`, and `LICENSE_YEAR=2026`, the API result is:

```json
{
  "status": 401,
  "code": 1001,
  "msg": {
    "ar": "تعذر تسجيل الدخول.",
    "en": "Unable to sign in."
  },
  "type": "tag:api.example.com,2026:example/demo-api/example-app/01/1001"
}
```

In HTML mode, the heading shows `تعذر تسجيل الدخول.` alongside status `401`. The reference button shows `#1001`, the English message, and the type on two adjacent text lines.

### English and missing-translation examples

With English active, `Label()` returns:

```json
{"en":"Unable to sign in."}
```

This is still a valid language map; its API `msg` remains an object even though it contains only English.

With Arabic active and the Arabic entry missing, `Label()` returns:

```json
{"ar":"Unable to sign in.","en":"Unable to sign in."}
```

If the English entry is missing, empty, or not a string, the English fallback is `Error 1001`. A valid Arabic entry can still be used:

```json
{"ar":"تعذر تسجيل الدخول.","en":"Error 1001"}
```

If neither entry is available, both labels use `Error 1001`. English is always represented when encoding succeeds.

## Error enums and translation discovery

### Public contract

```php
public function Code(): int;

public function Label(): string;
```

`ErrorEnumInterface` defines only these two methods. `ErrorEnumTrait` additionally supplies public static `PackageName()` and `TransSource()`, and protected static `EnglishLabels()`.

| Method | Behavior |
| --- | --- |
| `Code()` | Returns the integer-backed case's `$this->value`. |
| `Label()` | Returns a JSON string with English and, when different from `en`, the active normalized locale. |
| `PackageName()` | Finds the package by checking registered namespace prefixes from the enum namespace upward. |
| `TransSource()` | Combines optional package prefix and kebab-case enum short name. |
| `EnglishLabels()` | Finds, includes, and caches the English dictionary array. |

The trait assumes `$this->value` and `$this->name` exist as they do on backed enum cases. `Response` and the exception constructors do not accept an enum case directly as their message/code contract; pass `Code()` and `Label()` explicitly.

### Translation key construction

For `Accounts\Debug\AccountsErrors` in an existing package with ID `accounts`:

| Part | Result |
| --- | --- |
| Enum short name | `AccountsErrors` |
| `Str::ParamCase()` result | `accounts-errors` |
| Package prefix | `accounts::` |
| `TransSource()` | `accounts::accounts-errors` |
| Case name | `LOGIN_FAILED` |
| Full active-language key | `accounts::accounts-errors.LOGIN_FAILED` |

Package lookup tries `Services::FindPackageByModelNs()` and then `Services::FindPackageByControllerNs()` at each namespace level. If no package matches, a class under `Semiorbit\` uses `semiorbit`; other namespaces use an unqualified source and therefore the application's language directory.

An enum's PHP namespace and its package registry ID are different concepts. During normal startup, the framework initializes the configured packages through their existing service providers. The trait reads that existing namespace information; it does not register a new package or require an extra user registration step. The package ID becomes the translation prefix.

### Active-language lookup

`Label()` uses the existing translation mechanism:

```php
Lang::Trans(static::TransSource() . '.' . $this->name, [], 0, $english);
```

`Lang` parses `package::file.KEY`, loads dictionaries through `Finder`, and resolves the current locale/base-language entry. The enum passes its English text as the default and also rejects an empty or non-string translation result.

With the default `Config::LangExt()` value `.inc`, common package files are:

| Locale | Preferred file | Flat alternative |
| --- | --- | --- |
| `en` | `lang/en/accounts-errors.en.inc` | `lang/accounts-errors.en.inc` |
| `ar` | `lang/ar/accounts-errors.ar.inc` | `lang/accounts-errors.ar.inc` |
| `ar-eg` | `lang/ar/accounts-errors.ar-eg.inc` | `lang/accounts-errors.ar-eg.inc` |

For a regional locale, the locale-specific file sits in the **base-language directory** (`ar/` for `ar-eg`). If its key is absent, `Lang::Trans()` can use the base `ar` dictionary before the enum's English default. The resulting map is keyed by the active locale, for example `ar-eg`, even when the wording came from `ar`.

### English lookup and caching

English must be retained independently of the current language. `EnglishLabels()` uses the framework's existing `Finder::LookFor()` and package language paths without changing the active locale. It reads English dictionary strings directly rather than calling the active-language translation getter for English.

It tries these selectors, substituting the source name and configured extension:

```text
accounts::en/accounts-errors.en.inc
accounts::accounts-errors.en.inc
```

The cache is indexed by translation source. A non-array dictionary is treated as an empty array. Missing dictionaries are cached as empty arrays too; there is no explicit reload method in this trait.

For a registered package, lookup uses its language directory. For unqualified application enums, lookup uses the application's configured language directory. For the `semiorbit` prefix, Finder can additionally resolve framework language files; its framework path replaces `::` with `/`.

### What the enum does and does not store

The enum is the code catalog. The dictionaries are the wording catalog. HTTP status and private diagnostics belong to the call site. The trait does not allocate codes, validate application-wide uniqueness, or attach a status/type to an enum case.

Within an enum, backed values are unique. Across enum classes, maintain distinct codes where they share one application's error contract: type generation receives the application context and code, not the enum's namespace or name.

`Label()` builds a fresh language map when called. It includes English and the current locale, not every dictionary language. Only the exact normalized locale `en` produces an English-only map; an English regional locale such as `en-us` produces `en-us` and `en` entries. It supplies no placeholder parameters or plural count; messages in these dictionaries should be complete strings unless the surrounding application implements additional formatting.

### JSON encoding policy

The trait ends with:

```php
return json_encode($labels, JSON_UNESCAPED_UNICODE) ?: '';
```

`JSON_UNESCAPED_UNICODE` keeps valid Unicode text, including Arabic, readable. The current code does not use `JSON_THROW_ON_ERROR`, `JSON_INVALID_UTF8_SUBSTITUTE`, or `JSON_UNESCAPED_SLASHES`. Slash escaping is therefore ordinary JSON escaping and does not change decoded text.

Messages and dictionaries must contain valid UTF-8. If encoding fails, `Label()` returns an empty string; the response later uses its normal default-status-message fallback. An encoding failure is not converted into a new exception.

## ErrorMessage reference

`Semiorbit\Debug\ErrorMessage` accepts a string and exposes read methods. Use its static factory because the constructor is protected:

```php
use Semiorbit\Debug\ErrorMessage;



$message = ErrorMessage::Read('{"ar":"تعذر تسجيل الدخول.","en":"Unable to sign in."}');

$english = $message->English();

$arabic = $message->Language('ar');

$active = $message->ActiveLang();

$labels = $message->ToArray();

$raw = $message->Raw();
```

### Public methods

| Method | Return type | Behavior |
| --- | --- | --- |
| `Read(string $message)` | `static` | Final static factory; reuses the latest instance if the raw string matches. |
| `ActiveMessage()` | `?ErrorMessage` | Latest cached instance; `null` before any `Read()`. |
| `Raw()` | `string` | Exact original string, including JSON and original surrounding whitespace. |
| `English()` | `string` | Cached `en` label or the original plain message. |
| `ActiveLang()` | `string` | Calls `Language()` using current `Lang::ActiveLang()`, defaulting to `en`. |
| `Language(string $locale)` | `string` | Exact locale, base language, then English. |
| `ToArray()` | `array` | Cached language map; plain messages become `['en' => $message]`. |
| `IsTranslated()` | `bool` | Whether parsing accepted a language map. |

There is no `ToJson()` method. `Raw()` already supplies the original JSON string when JSON was given. `ToArray()` provides values for inclusion in another JSON structure.

### Plain text and invalid-map handling

```php
$message = ErrorMessage::Read('Unable to sign in.');

$english = $message->English();

$arabic = $message->Language('ar');

$labels = $message->ToArray();

$translated = $message->IsTranslated();
```

Both reads return `Unable to sign in.`; `$labels` is `['en' => 'Unable to sign in.']`; `$translated` is `false`. Plain strings are accepted without translation or JSON conversion.

The final protected `Process()` method first initializes that plain-message representation. It attempts decoding only when the trimmed input is nonempty and starts with `{`.

A decoded language map is accepted only when:

1. The decoded value is an array.
2. It contains an `en` value that is a string and nonempty after trimming.
3. Every value in the array is a string.

The decoder uses `json_decode($json, true)` without throwing. A failed decode or rejected map retains the original input as plain text.

| Input | Interpretation |
| --- | --- |
| `Unable to sign in.` | Plain message. |
| `{"en":"Unable to sign in."}` | Valid map; `IsTranslated()` is `true`. |
| `{"ar":"تعذر تسجيل الدخول.","en":"Unable to sign in."}` | Valid map. |
| `{"ar":"تعذر تسجيل الدخول."}` | Plain message because English is missing. |
| `{"en":""}` | Plain message because English is empty. |
| `{"en":"Message","code":1001}` | Plain message because one value is not a string. |
| `["Message"]` | Plain message; top-level arrays are not decoded as maps. |
| `{"en":` | Plain message; malformed JSON. |
| Empty string | Plain empty message; the response supplies defaults separately. |

`IsTranslated()` describes the accepted **format**, not whether two different languages exist or their text differs. `{"en":"Message"}` and a map with identical English/Arabic wording both return `true`.

The parser validates value types and English presence, not locale-key syntax. For manually authored maps, use normalized lowercase keys such as `ar`, `ar-eg`, and `en`.

### Locale resolution

`Language()` lowercases its requested locale and replaces `_` with `-`. It tries a nonempty exact-locale label, then a nonempty base-language label, then English.

Given:

```json
{
  "ar-eg": "تعذر تسجيل الدخول إلى حسابك.",
  "ar": "تعذر تسجيل الدخول.",
  "en": "Unable to sign in."
}
```

| Call | Returned text |
| --- | --- |
| `Language('AR_EG')` | `تعذر تسجيل الدخول إلى حسابك.` |
| `Language('ar-sa')` | `تعذر تسجيل الدخول.` |
| `Language('ar')` | `تعذر تسجيل الدخول.` |
| `Language('fr')` | `Unable to sign in.` |

Requested locale normalization does not rewrite the supplied map's keys. Non-English empty/whitespace values are permitted by parsing but skipped during language selection. Accepted strings retain their original whitespace; the getter tests trimmed text for emptiness rather than trimming its return value.

`ActiveLang()` selects from the cached map at call time. It does not retranslate or load another dictionary. A message created with `ar` and `en` will fall back to English if later read with `fr` active, even when a French dictionary exists elsewhere. Call the enum's `Label()` under the desired request language to generate that language's text.

### Last-instance cache

The cache is a **single latest instance**, not a collection of every message:

```php
$first = ErrorMessage::Read('First message');

$same = ErrorMessage::Read('First message');

$different = ErrorMessage::Read('Second message');

$firstAgain = ErrorMessage::Read('First message');
```

`$first === $same` is true. `$different` replaces the active cache entry. `$firstAgain` is a new instance because the active entry contains the second message; `$first === $firstAgain` is false.

Comparison uses the exact raw string. Different JSON whitespace or key order can produce different instances even when the decoded labels are equivalent. Parsing occurs once per created object, and its decoded labels are retained on that object.

An exception stores its own `ErrorMessage` reference. Replacing the active cache therefore does not erase an older exception's labels. `ActiveMessage()` is a convenience accessor, not the way to retrieve a particular exception's message; use `getErrorMessage()` for that.

| Internal member | Visibility / role |
| --- | --- |
| `$_Instance` | Private static latest-instance reference. |
| `$_Message` | Protected raw string. |
| `$_Labels` | Protected cached labels. |
| `$_Translated` | Protected format flag. |
| `__construct()` | Protected initialization and parsing. |
| `Read()` | Final public static creation/cache entry point. |
| `Process()` | Final protected parser. |

The class remains inheritable. Its current `Read()` compares only the raw message; it does not compare concrete classes, and the inherited cache is shared. Because it returns `static`, mixing parent and subclass reads for the same raw text can cause a return-type error when a subclass call receives a cached parent object. Subclass authors must account for that constraint; a private property or a final factory does not make the cache class-specific.

## Exception classes and methods

### Class hierarchy

| Class | Parent | Framework additions |
| --- | --- | --- |
| `AppException` | `\Exception` | Implements `AppExceptionInterface`; uses `AppExceptionTrait`. |
| `AppRuntimeException` | `\RuntimeException` | Implements the same interface; uses the same trait. |
| `AppAbortException` | `AppRuntimeException` | Inherits its constructor/message behavior; caught separately by `AppService::Run()`. |

`AppRuntimeException` and `AppException` carry the same response information. Their PHP base types allow different catch policies. `AppAbortException` is an application termination signal at the framework's catch boundary, not a different message format.

### Constructor order

All three accept this order:

```php
public function __construct(

	$code,

	$message = "",

	$debug_msg = "",

	$http_status_code = 503,

	Throwable $previous = null

);
```

Unlike native PHP exceptions, the application error **code comes first**.

| Argument | Meaning / default |
| --- | --- |
| `$code` | Application error code, required. |
| `$message` | Public plain text or JSON language-map string; default `""`. |
| `$debug_msg` | Private diagnostics for debug responses; default `""`. |
| `$http_status_code` | HTTP status; default `503`. |
| `$previous` | Optional causal `Throwable`; default `null`. |

The constructors call:

```php
parent::__construct($this->setErrorMessage($message)->English(), $code, $previous);

$this->setHttpStatusCode($http_status_code)->setDebugMsg($debug_msg);
```

Thus native exception output receives English, while the trait retains the original map. Empty public text remains empty in the exception; default display wording is selected later by the response.

### Message and metadata methods

| Method | Result |
| --- | --- |
| `getMessage()` | Native exception message initialized to English text. |
| `getCode()` | Native exception code. |
| `getPrevious()` | Previous throwable, when provided. |
| `getErrorMessage(): ErrorMessage` | Retained message object; lazily reads native `getMessage()` if missing. |
| `setErrorMessage(string $message): ErrorMessage` | Stores and returns the parsed message object. |
| `getDebugMsg()` | Stored diagnostics. |
| `setDebugMsg($debug_msg)` | Stores diagnostics and returns the exception. |
| `getHttpStatusCode()` | Stored HTTP status. |
| `setHttpStatusCode($HttpStatusCode)` | Stores HTTP status and returns the exception. |

`setErrorMessage()` deliberately returns **ErrorMessage**, allowing `->English()`. It is not an exception fluent setter:

```php
$english = $exception->setErrorMessage($rawMessage)->English();

$exception->setHttpStatusCode(401)->setDebugMsg('Authentication failed.');
```

Calling `setErrorMessage()` after construction updates the retained object used by `Response`; it does **not** rewrite the native exception's already initialized `getMessage()`. Set the intended public message in the constructor when both representations must match.

`AppExceptionInterface` currently declares the constructor and HTTP/debug getters/setters. It does not declare `getErrorMessage()` or `setErrorMessage()`; those are supplied by the trait.

## Throwing exceptions and aborting execution

### Direct exception construction

```php
use Accounts\Debug\AccountsErrors;
use Semiorbit\Debug\AppException;



$error = AccountsErrors::LOGIN_FAILED;

throw new AppException(

	$error->Code(),

	$error->Label(),

	'Authentication provider rejected the credentials.',

	401

);
```

Use `AppRuntimeException` when the runtime-exception base type fits the application's catch policy. Use `AppAbortException` directly when abort behavior is needed and, for example, a previous throwable must also be supplied.

### Application helpers

The helpers use **HTTP status first**, unlike the constructors:

```php
Application::Exception($status, $message = '', $code = 0, $debug_message = '');

Application::Abort($status, $message = '', $code = 0, $debug_message = '');
```

`Application::Exception()` throws `AppException`. `Application::Abort()` throws `AppAbortException` and is declared `never`. Both replace an omitted/zero application code with the HTTP status.

Examples:

```php
Application::Exception(401, $error->Label(), $error->Code());
```

```php
Application::Abort(403, 'You do not have permission to access this page.', 2001);
```

```php
Application::Abort(404);
```

The final example produces effective status/code `404` with default text `Not Found`.

The global helper `abort($code, $message = '')` forwards to `Application::Abort($code, $message)`. Its first argument is used as the HTTP status, despite its parameter name. It does not expose the separate application-code or debug-message arguments; use the class helper for those.

### What Run does with each exception

Inside `AppService::Run()`'s try/catch boundary:

| Exception | Output-buffer handling | Result |
| --- | --- | --- |
| `AppAbortException` | Attempts to clear all open output buffers. | Builds response, optionally sanitizes, echoes it, then exits PHP execution. |
| Other `\Exception` | Closes the current buffer. | Builds response, reaches the normal final sanitization/output/return path. |

Both branches call `Response::SendException($e, false)`. On the ordinary exception path, output respects `Run()`'s `$flush_output` argument. On the abort path, the error is echoed and execution exits regardless of that flag.

This distinction matters for HMVC subrequests: an ordinary exception produces the subrequest's error output and can return to its caller; an abort ends the enclosing execution too. When an action throws, that action's remaining statements and the normal action-output `onRun()` step are skipped.

The abort helper itself throws; the exit occurs when the framework catches the abort exception. Application code that catches it earlier takes responsibility for what happens next. Catch the specific exception first when catching multiple application exception types, since it is also a `RuntimeException` and an `Exception`.

The current boundary catches `\Exception`, not every `\Throwable`. Native PHP `Error`/`TypeError` failures are not automatically rendered by this boundary. Failures outside its try block also need their own handling.

## Response processing and HTTP defaults

### Public response methods

| Method | Behavior |
| --- | --- |
| `Exception(\Exception $e): static` | Prepares exception content and status on the response; returns the response. |
| `SendException(\Exception $e, $flush_output = true)` | Creates a response, prepares it, then calls `Send()`. |
| `Json($data, $options = JSON_UNESCAPED_UNICODE): static` | Sets JSON content type and encodes data. |
| `Send(bool $flush_output = true)` | Sends headers; echoes content when enabled; returns stored content. |

`ErrorHandler()` and `ErrorType()` are protected implementation methods. Applications normally enter through `Exception()`, `SendException()`, or the throwing helpers rather than calling them directly.

### Framework exceptions versus ordinary exceptions

If an exception implements `AppExceptionInterface`, `Response::Exception()` uses its code, HTTP status, debug message, and public message. It checks for `getErrorMessage()` with `method_exists()`; if present, it uses `->Raw()`, otherwise native `getMessage()`.

For an ordinary exception, `Response` uses:

- Its native code as the application-code input.
- An empty public-message input, leading to a default HTTP message.
- Its native message as debug diagnostics.
- No explicit HTTP status, allowing status inference.

An ordinary exception's raw diagnostic text is therefore not selected as the public message. For a code-zero `RuntimeException('Database connection failed.')`, the production body uses status/code `503` and `Service Unavailable`; the original text appears in the debug fields when enabled.

### Effective status, code, and message

`ErrorHandler()` applies these rules in order:

1. If no truthy HTTP status was supplied, use the application code when it exists in `ResponseStatus::STATUS_MESSAGES`; otherwise use `503`.
2. Use the supplied public message when truthy; otherwise use `DefaultStatusMessage($http_status_code)`.
3. Parse that selected string with `ErrorMessage::Read()`.
4. Set body `status` to the selected HTTP status.
5. Set body `code` to the integer application code when nonzero; otherwise use the HTTP status.
6. Generate `type` using the supplied code when truthy, otherwise the HTTP status.
7. Add diagnostics only in debug mode.
8. Call `setStatus($http_status_code)` with its normal HTTP reason phrase.
9. Produce API JSON or an HTML error view according to `Config::ApiMode()`.

| Input | Effective result |
| --- | --- |
| `AppException(1001, $label, '', 401)` | Status `401`, code `1001`, supplied message. |
| `AppException(1001, $label)` | Status `503`, code `1001`; constructor default supplies `503`. |
| `AppException(404, '')` | Status `503`, code `404`, `Service Unavailable`; constructor status default still applies. |
| `Application::Abort(404)` | Status/code `404`, `Not Found`. |
| Ordinary `RuntimeException('Internal detail', 404)` | Status/code `404`, `Not Found`; native message is debug text. |
| Ordinary `RuntimeException('Internal detail', 1001)` | Status `503`, code `1001`, `Service Unavailable`. |
| Ordinary `RuntimeException('Internal detail')` | Status/code `503`, `Service Unavailable`. |

An empty message is not replaced by `An error occurred!` in the normal response path; it is replaced by the selected status phrase. The default view has its own last fallback for a missing/empty `msg`. PHP's truthiness check also treats the literal string `'0'` as absent here.

Supply valid integer application codes and valid HTTP statuses. Setters do not establish a domain-specific validation layer for them. Default phrases come from the framework's status table and are not localized through the enum dictionaries.

### Error type construction

The current format is:

```text
tag:<host>,<license-year>:<composer-name>[/<app-id>][/<db-code>]/<code>
```

| Part | Current source |
| --- | --- |
| Host | `Url::Host()`, with trailing `/` removed. |
| License year | Main application's `LicenseYear()`. |
| Composer name | Main app service's `Composer('name')`. |
| Optional app ID | Main application class's `Id()`, when that method exists and its string is nonempty. |
| Optional database code | Main application class's `DbCode()`, with the same conditions. |
| Code | Supplied error code or status fallback. |

For the standard `Application` class, `Id()` reads and caches `APP_ID`; `DbCode()` reads and caches `DB_CODE`; `LicenseYear()` reads and caches `LICENSE_YEAR`, falling back to the current `date('Y')` when absent/empty.

The Composer name is the **application's** Composer name, not automatically the framework name or the package containing the error enum. A name such as `example/demo-api` contributes both slash-separated components. Missing app ID or database code contributes no placeholder segment.

The type identifies an error in an application/deployment context; it is not a request ID or an occurrence timestamp. It does not contain the enum case name, exception class, translated wording, or a random identifier.

The type can change when the host, Composer name, app ID, database code, year, or error code changes. Set `LICENSE_YEAR` explicitly when the identifier must remain stable across calendar years. `Url::Host()` uses the current host information, including any port supplied in `HTTP_HOST`; the generator does not independently normalize it into an RFC authority or encode the metadata components. Use appropriate stable metadata for the deployment.

## API responses and client handling

### Base response fields

`Config::ApiMode()` selects the JSON branch. The framework's `Json()` method sets `Content-Type: application/json; charset=UTF-8` and encodes with `JSON_UNESCAPED_UNICODE` by default.

| Field | Value |
| --- | --- |
| `status` | Selected HTTP status, normally an integer. |
| `code` | Integer application code or status fallback. |
| `msg` | Plain string or decoded language-map object. |
| `type` | Generated string identifier. |

`setStatus()` receives the HTTP status alone. Public messages belong in the body; localized text and serialized maps are not passed as HTTP status-line reason phrases.

For a plain message:

```json
{
  "status": 403,
  "code": 2001,
  "msg": "You do not have permission to access this page.",
  "type": "tag:api.example.com,2026:example/demo-api/example-app/01/2001"
}
```

For an accepted language map, `msg` becomes a nested object as shown in the complete example. It is not an escaped JSON string such as `"{\"ar\":...}"`: `ErrorMessage::ToArray()` supplies the decoded values before the outer encoding.

`ToArray()` always returns an array even for plain text. The response deliberately checks `IsTranslated()` so existing plain API messages retain their string representation.

### Debug fields

With `Config::DebugMode()` enabled, the API response additionally includes:

| Field | Content |
| --- | --- |
| `debug_msg` | Explicit framework diagnostics, or native message for an ordinary exception. |
| `file` | Exception file, or empty string without an exception. |
| `line` | Exception line, or empty string without an exception. |
| `trace` | `getTraceAsString()`, or empty string without an exception. |

These fields are omitted entirely when debug mode is off. They are not included as empty production placeholders.

### Client localization example

A JavaScript client must support both forms of `msg`. This helper reads a received map; it does not fetch translations:

```js
/**
 * Read localized public text from a Semiorbit Framework error response.
 *
 * @param {Object} error The parsed error response.
 * @param {string} locale The requested display locale.
 * @returns {string} Available localized text, English, or an empty string.
 */

export function errorText(error, locale = 'en') {

    const message = error?.msg;

    if (typeof message === 'string') return message;

    if (!message || typeof message !== 'object' || Array.isArray(message)) return '';

    const normalized = locale.toLowerCase().replaceAll('_', '-');

    const language = normalized.split('-')[0];

    return [message[normalized], message[language], message.en]
        .find(value => typeof value === 'string' && value.trim() !== '') ?? '';

}
```

Usage with the localized response:

```js
const arabicText = errorText(error, 'ar');

const englishSupportText = errorText(error, 'en');

const applicationCode = error.code;

const errorType = error.type;
```

Use the HTTP response status for transport handling and `code`/`type` for application behavior. Do not compare translated sentences to determine which failure occurred. Switching the frontend to another language can only select labels already returned; request a message generated under that language when it is absent.

### Request-language selection

The current `Request::DetectLang()` considers a supported leading path language, the configured language request parameter, then the already active/default language. A supported path language overrides the parameter. `Request::Load()` applies the result with `Lang::UseLang()` before loading the controller.

The error-message class does not inspect `Accept-Language`. The framework's current `DetectLang()` does not itself perform header negotiation either. If an application uses that header, its integration must select the framework language before enum labels are generated.

### Existing direct JSON error responses

The four-field envelope above describes the exception response path. A controller that directly calls `Response::Json()` controls its own shape.

In particular, the built-in `HttpErrorController::Index()` currently produces a legacy 404 body with `status: "error"`, numeric `code`, and a plain `msg`, without `type`. It is not routed through `ErrorHandler()`. Consumers of that route must account for its existing contract rather than assuming every JSON error already has the exception envelope.

## HTML error pages and the copy button

When API mode is off, `Response` tries these view selectors in order:

```text
errors/<HTTP status>
errors/default
```

It disables the layout and passes the error content as view parameters. Application views can override the framework's error views using normal Finder resolution. A status-specific view is responsible for honoring the same parameters and localization contract.

The framework includes `errors/400.phtml`, which delegates to `errors/default` and forwards its parameters.

### Default page data

The default view:

1. Requests `Lang::UseLang('en')` when `LANG` has not been defined; normal supported-language validation still applies.
2. Computes display status from `status`, then `code`, then `503`.
3. Normalizes the active locale for the HTML `lang` attribute.
4. Reads the raw `msg` through `ErrorMessage`, using `An error occurred!` if the input is missing/empty.
5. Displays `ActiveLang()` in the main heading and `English()` in the reference button.

The response passes the original message string to HTML views. This preserves the shared parser contract; views do not need their own JSON-decoding logic.

### Visible elements

| Element | Content |
| --- | --- |
| Main status marker | HTTP status, for example `401`. |
| Main heading | Active-language public message with `dir="auto"`. |
| Reference badge | Application code prefixed by `#`, for example `#1001`. |
| First reference line | English public message. |
| Second reference line | Error type. |
| Icon | Status-specific icon with a default fallback. |

The icon is selected through `Render::Widget(['errors/icons/' . $error_status, 'errors/icons/default'])`. The page uses the `errors/head` widget for shared styling.

The reference box is left-to-right. Its message/type lines wrap when necessary. The code badge uses stretched flex alignment and `margin-block: -2px`, extending it two pixels above and below the adjacent two-line text block.

### Copy behavior

Clicking the reference button calls `navigator.clipboard.writeText()` with exactly:

```text
Unable to sign in.
tag:api.example.com,2026:example/demo-api/example-app/01/1001
```

The script trims the two DOM text values and joins them with a newline. It does not add the visible `#1001` badge as a third line; the type already contains the code.

On success, the badge temporarily reads `Copied`. On failure, it reads `Copy failed`. The original badge returns after **1,500 milliseconds**. A new click clears the previous restoration timer. Clipboard access depends on the browser's clipboard availability and permissions; there is no secondary clipboard implementation in the current view.

The heading, English text, type, HTML locale attribute, and displayed diagnostic/file text are escaped with `htmlspecialchars(..., ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')`. This HTML escaping is separate from JSON encoding. `ENT_SUBSTITUTE` concerns malformed HTML input; it is not `JSON_INVALID_UTF8_SUBSTITUTE`.

### Debug page

With debug mode enabled, the view additionally displays the exception code, diagnostic text (falling back to the active public message), file/line, and trace widget. For HTML, the response provides the trace as `getTrace()`'s array rather than the API trace string.

The trace widget renders entries with expandable sections; application frames and framework frames have different highlighting. The default view does not localize its debug labels or the button's `Copied` / `Copy failed` text through the enum dictionaries.

## Debugging and logging

Public messages and diagnostics serve different audiences. Put user-facing guidance in the message argument and internal details in `$debug_msg`. `Config::DebugMode()` gates the additional fields and sections in the response; it does not hide the public code, message, or type.

The native exception message is English for exceptions constructed with the framework trait. Logging `$exception->getMessage()` therefore records readable text rather than a serialized bilingual object.

Example inside an exception catch block:

```php
use Semiorbit\Debug\FileLog;



FileLog::Error($exception->getCode(), __METHOD__, $exception->getMessage());
```

`FileLog::Error()` writes through the active logger. `FileLog` defaults to the application's `var/log/` directory. Call `getErrorMessage()->Raw()` when the original language-map string is needed, or `->ToArray()` when structured labels are needed.

The exception rendering path does not automatically call `FileLog`. Rendering an error, assigning a debug message, and writing a log are separate operations. Likewise, `ActiveMessage()` is only the latest parser cache entry; it is not a log history.

A previous throwable is retained by PHP and available through `getPrevious()`. The response uses the current exception's file, line, and trace; it does not emit a separate structured previous-exception chain.

Debug output can contain implementation details and trace arguments. The trace widget renders raw trace values, so its output should be treated as developer diagnostics; the public-text escaping described above is not a guarantee that every trace field is escaped.

## Compatibility and implementation boundaries

| Situation | Current behavior |
| --- | --- |
| Existing plain public messages | Remain accepted; API `msg` remains a string. |
| Accepted maps with only `en` | API `msg` is an object; format is considered translated. |
| Missing localized entry | Falls back to English. |
| Missing English enum entry | Enum generates `Error <code>`. |
| Malformed JSON or invalid map | Parser retains the original input as plain text. |
| JSON encode/decode failure | No JSON exception is explicitly thrown; existing fallbacks apply. |
| Old custom `AppExceptionInterface` implementation | Response falls back to native `getMessage()` when `getErrorMessage()` does not exist. |
| Custom implementation with `getErrorMessage()` | Must return an object supporting the response's `Raw()` call. |
| Custom HTML status view | Must handle the raw message string or reuse `ErrorMessage`. |
| Ordinary `\Exception` | Default public status phrase; native message becomes diagnostics. |
| Native `\Error` / `\TypeError` | Outside the current `\Exception` catch/response contract. |
| Direct `Json()` controller result | Preserves the controller's own response structure. |

The message object does not implement `__toString()`, auto-detect every JSON document as an error, or fetch translations. Supply its `Raw()`, `English()`, or `ActiveLang()` result wherever a string is expected.

The caches are in-process static state. Normal separate PHP executions start fresh; a persistent worker keeps state until it resets/restarts. `ErrorMessage` has no explicit cache reset, and the enum's English dictionary cache has no reload API. `Lang` also maintains dictionary/key caches; selecting a new active locale does not by itself establish a complete cache-reset workflow. Resolve request language early and account for these caches in custom persistent-worker or multi-language-in-one-execution designs.

`Response::Json()` uses ordinary `json_encode()` and does not introduce a second encoding-failure response. Valid UTF-8 and JSON-encodable values are required for the final body too; the enum's empty-string fallback only handles its own label encoding.

Application creation and rendering require application metadata/configuration. A response-formatting failure occurring inside a catch branch is not caught again by that same try/catch. These methods are not a standalone replacement for PHP's global fatal-error handling.

## Troubleshooting

| Symptom | Check |
| --- | --- |
| Arabic page shows English | Supported active language; enum placed under the existing package namespace; Arabic dictionary path; exact `UPPER_SNAKE_CASE` key; whether the map was generated before language selection. |
| English shows `Error 1001` | English dictionary exists, returns an array, and contains a nonempty string for `LOGIN_FAILED`. |
| JSON text appears literally | Map has a nonempty string `en`; every value is a string; JSON is valid and top-level object-shaped. |
| `msg` contains only `en` | English was active when `Label()` ran, or only an English map was supplied. |
| Regional locale misses translation | Use normalized map keys; place regional dictionary under the base-language folder; check supported locales. |
| Custom code produces HTTP 503 | Direct constructors default to 503; explicitly supply HTTP status or use the status-first application helper. |
| Raw exception detail is absent in production | Ordinary exceptions intentionally use public status phrases; diagnostics are debug-only. |
| Changing one message affects the active cache | Cache stores only the latest instance; retrieve older exception data from its own `getErrorMessage()`. |
| Copy button says `Copy failed` | Browser clipboard API/permissions/context; current script has no fallback. |
| Type changes between environments | Host, application Composer name, app ID, database code, license year, or code differs. |
| Type changes with the calendar year | Set a fixed `LICENSE_YEAR`; the default is the current year when first read. |
| An API 404 has `status: "error"` and no `type` | Built-in `HttpErrorController` uses its direct legacy JSON response. |
| Dictionary edit is not reflected in a persistent process | English/translation caches persist; no explicit error-enum dictionary reload is provided. |

## Source reference

The following paths define the behavior documented here:

| File | Responsibility |
| --- | --- |
| `src/core/Debug/ErrorEnumInterface.php` | Error enum contract. |
| `src/core/Debug/ErrorEnumTrait.php` | Enum labels, package discovery, English cache. |
| `src/core/Debug/ErrorMessage.php` | Parser, language selection, last-instance cache. |
| `src/core/Debug/AppExceptionInterface.php` | Exception constructor and HTTP/debug contract. |
| `src/core/Debug/AppExceptionTrait.php` | Retained message and metadata. |
| `src/core/Debug/AppException.php` | Native exception integration. |
| `src/core/Debug/AppRuntimeException.php` | Runtime exception integration. |
| `src/core/Debug/AppAbortException.php` | Abort subtype. |
| `src/core/Base/Application.php` | Throwing helpers and environment-backed identifiers. |
| `src/core/Base/AppService.php` | Catch boundaries, buffering, output, Composer metadata. |
| `src/core/Http/Response.php` | Exception envelope, JSON/HTML selection, type construction. |
| `src/core/Http/ResponseHeaders.php` | HTTP status, reason phrase, headers. |
| `src/core/Http/ResponseStatus.php` | Status constants and default phrase table. |
| `src/core/Http/Url.php` | Host information. |
| `src/core/Http/Request.php` | Request language detection and controller execution. |
| `src/core/Controllers/HttpErrorController.php` | Direct legacy API 404 response. |
| `src/core/Translation/Lang.php` | Translation keys and locale/base-language dictionaries. |
| `src/core/Component/Services.php` | Package namespace indexes. |
| `src/core/Component/Package.php` | Existing package namespace and language-path metadata. |
| `src/core/Component/Finder.php` | Application/package/framework file resolution. |
| `src/core/Config/Default/lang.inc` | Default language directory and extension. |
| `src/views/errors/default.phtml` | Localized page, support reference, clipboard behavior. |
| `src/views/errors/head.widget.phtml` | Page and reference-button styling. |
| `src/views/errors/400.phtml` | Status-specific delegation to default view. |
| `src/views/errors/trace.widget.phtml` | HTML trace display. |
| `src/core/Debug/FileLog.php` | Optional file logging. |
| `src/helpers/global.php` | Global `abort()` helper. |

Related reference: [App Events Execution Order](APP_EVENT_EXECUTION_ORDER.md).

External references: [RFC 9457](https://datatracker.ietf.org/doc/html/rfc9457) and [RFC 4151](https://www.rfc-editor.org/rfc/rfc4151.html).

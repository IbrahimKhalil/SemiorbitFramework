<?php

namespace Semiorbit\Debug;

use Semiorbit\Translation\Lang;



/**
 * Read plain error messages or JSON language maps.
 */

class ErrorMessage
{

    private static ?self $_Instance = null;

    protected string $_Message;

    protected array $_Labels = [];

    protected bool $_Translated = false;



    /**
     * Decode the message once.
     */

    protected function __construct(string $message)
    {

        $this->_Message = $message;

        $this->Process();

    }



    /**
     * Reuse the latest instance when its raw message matches.
     */

    final public static function Read(string $message): static
    {

        $instance = static::$_Instance;

        if (

            $instance !== null

            && $instance->_Message === $message

        ) return $instance;


        return static::$_Instance = new static($message);

    }



    /**
     * Active Instance
     *
     * @return ErrorMessage|null
     */

    public static function ActiveMessage(): ?ErrorMessage
    {
        return static::$_Instance;
    }



    /**
     * Return the original message, including its JSON when supplied.
     */

    public function Raw(): string
    {
        return $this->_Message;
    }



    /**
     * Return the English message.
     */

    public function English(): string
    {
        return $this->_Labels['en'];
    }



    /**
     * Return the active locale's message, falling back to English.
     */

    public function ActiveLang(): string
    {
        return $this->Language((string) (Lang::ActiveLang() ?: 'en'));
    }



    /**
     * Resolve a locale, then its base language, then English.
     */

    public function Language(string $locale): string
    {

        $locale = strtolower(str_replace('_', '-', $locale));

        $language = explode('-', $locale, 2)[0];

        $message = $this->_Labels[$locale] ?? '';

        if (trim($message) !== '') return $message;

        $message = $this->_Labels[$language] ?? '';


        return (trim($message) !== '') ? $message : $this->English();

    }



    /**
     * Return the cached language map.
     */

    public function ToArray(): array
    {
        return $this->_Labels;
    }



    /**
     * Whether the original message contains a valid language map.
     */

    public function IsTranslated(): bool
    {
        return $this->_Translated;
    }



    /**
     * Accept string-valued language maps containing a nonempty English label.
     */

    final protected function Process(): void
    {

        $this->_Labels = ['en' => $this->_Message];

        $this->_Translated = false;

        $json = trim($this->_Message);

        if ($json === '' || $json[0] !== '{') return;

        $labels = json_decode($json, true);

        if (

            !is_array($labels)

            || !isset($labels['en'])

            || !is_string($labels['en'])

            || trim($labels['en']) === ''

            || array_filter($labels, 'is_string') !== $labels

        ) return;

        $this->_Labels = $labels;

        $this->_Translated = true;

    }

}
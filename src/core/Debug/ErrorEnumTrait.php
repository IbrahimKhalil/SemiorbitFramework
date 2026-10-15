<?php

namespace Semiorbit\Debug;

use Semiorbit\Component\Finder;
use Semiorbit\Component\Services;
use Semiorbit\Config\Config;
use Semiorbit\Support\Path;
use Semiorbit\Support\Str;
use Semiorbit\Translation\Lang;



/**
 * Bilingual messages for integer-backed error enums.
 *
 * @mixin \BackedEnum
 */

trait ErrorEnumTrait
{

    /**
     * Return the application error code.
     */

    public function Code(): int
    {
        return $this->value;
    }


    /**
     * Return a JSON language map containing the active locale and English.
     * @return string
     */

    public function Label(): string
    {

        $english = static::EnglishLabels()[$this->name] ?? '';

        if (!is_string($english) || trim($english) === '') $english = 'Error ' . $this->Code();

        $locale = strtolower(str_replace('_', '-', (string) (Lang::ActiveLang() ?: 'en')));

        $labels = ['en' => $english];

        if ($locale !== 'en') {

            $label = Lang::Trans(static::TransSource() . '.' . $this->name, [], 0, $english);

            $labels = [
                $locale => (is_string($label) && trim($label) !== '') ? $label : $english,
                'en' => $english
            ];

        }


        return json_encode($labels, JSON_UNESCAPED_UNICODE) ?: '';

    }



    /**
     * Find the package through the enum's namespace.
     */

    public static function PackageName(): string
    {

        $namespace = Path::ClassNamespace(static::class);

        while ($namespace !== '') {

            $package = Services::FindPackageByModelNs($namespace)

                ?? Services::FindPackageByControllerNs($namespace);


            if ($package !== null && $package !== '') return (string) $package;

            $position = strrpos($namespace, '\\');

            $namespace = ($position === false) ? '' : substr($namespace, 0, $position);

        }


        return (str_starts_with(static::class, 'Semiorbit\\')) ? 'semiorbit' : '';

    }



    /**
     * Build the package-qualified translation source.
     */

    public static function TransSource(): string
    {

        $package = static::PackageName();

        $file = Str::ParamCase(Path::ClassShortName(static::class));


        return (($package !== '') ? $package . '::' : '') . $file;

    }



    /**
     * Cache the English dictionary using the framework's translation paths.
     */

    protected static function EnglishLabels(): array
    {

        static $dictionaries = [];

        $source = static::TransSource();

        if (isset($dictionaries[$source])) return $dictionaries[$source];

        $package = static::PackageName();

        $prefix = ($package !== '') ? $package . '::' : '';

        $file = Str::ParamCase(Path::ClassShortName(static::class));

        $extension = Config::LangExt();

        $path = Finder::LookFor([

            $prefix . 'en/' . $file . '.en' . $extension,

            $prefix . $file . '.en' . $extension

        ], Finder::Lang, true, $package !== 'semiorbit');

        $labels = ($path) ? include $path['path'] : [];

        $dictionaries[$source] = (is_array($labels)) ? $labels : [];


        return $dictionaries[$source];

    }

}
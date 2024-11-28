<?php

namespace Semiorbit\Support;

use Exception;
use Semiorbit\Component\Services;
use Semiorbit\Translation\Lang;

trait OptionsListEnum
{


    /**
     * Check usage in an enum
     */
    public static function IsEnum(): bool
    {
        return  enum_exists(static::class);
    }

    public static function ToArray($trans_labels = true)
    {

        $statuses = static::cases();

        return array_reduce($statuses, fn($carry, $status) => $carry + [$status->value => $trans_labels ? $status->label() : $status->name], []);

    }


    public function label(): string
    {

        return static::TransSource() ?  Lang::Trans(static::TransSource(). '.' . $this->name)

            : constant($this->name);

    }

    public static function PackageName(): string
    {
        return Services::FindPackageByModelNs(Path::ClassNamespace(static::class));
    }

    public static function TransSource(): ?string
    {
        return static::PackageName() . '::'. Str::ParamCase(Path::ClassShortName(static::class));
    }


}

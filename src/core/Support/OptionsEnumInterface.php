<?php

namespace Semiorbit\Support;

interface OptionsEnumInterface
{
    public function Label(): string;

    public function Link(): string;


    public static function ToArray(): array;

    public static function ToLinkArray(): array;

    public static function ToFullArray(): array;

    public static function ToArraySubset(array $options): array;


}

<?php

namespace App\Enums;

enum ActivitySource: string
{
    case Web = 'web';
    case Android = 'android';

    public function label(): string
    {
        return match ($this) {
            self::Web => 'Web',
            self::Android => 'APK',
        };
    }
}

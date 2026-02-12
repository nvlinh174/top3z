<?php

namespace App\Enums;

enum GeneralStatus: int
{
    case ACTIVE = 1;
    case INACTIVE = 2;

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Hoạt động',
            self::INACTIVE => 'Không hoạt động',
        };
    }

    public static function toArray(bool $includeAll = false): array
    {
        $array = $includeAll ? ['all' => 'Tất cả'] : [];

        foreach (self::cases() as $status) {
            $array[$status->value] = $status->label();
        }

        return $array;
    }
}

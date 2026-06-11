<?php

namespace App\Enums;

enum CommentStatus: int
{
    case Active = 1;
    case Hidden = 2;

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Hiển thị',
            self::Hidden => 'Ẩn',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function toArray(bool $includeAll = false): array
    {
        $array = $includeAll ? ['all' => 'Tất cả'] : [];

        foreach (self::cases() as $status) {
            $array[$status->value] = $status->label();
        }

        return $array;
    }
}

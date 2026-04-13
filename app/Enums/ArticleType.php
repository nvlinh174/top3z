<?php

namespace App\Enums;

enum ArticleType: int
{
    case Article = 1;
    case Announcement = 2;

    public function label(): string
    {
        return match ($this) {
            self::Article => 'Bài viết',
            self::Announcement => 'Thông báo',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function toArray(bool $includeAll = false): array
    {
        $array = $includeAll ? ['all' => 'Tất cả'] : [];

        foreach (self::cases() as $case) {
            $array[$case->value] = $case->label();
        }

        return $array;
    }
}

<?php

namespace App\Enums;

enum ArticleModerationStatus: int
{
    case Draft = 0;
    case Pending = 1;
    case Approved = 2;
    case Rejected = 3;

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Bản nháp',
            self::Pending => 'Chờ duyệt',
            self::Approved => 'Đã đăng',
            self::Rejected => 'Bị từ chối',
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

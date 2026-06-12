<?php

namespace App\Enums;

enum CommunityFeedSort: string
{
    case Latest = 'latest';
    case Views = 'views';
    case Likes = 'likes';

    public function label(): string
    {
        return match ($this) {
            self::Latest => 'Mới nhất',
            self::Views => 'Xem nhiều',
            self::Likes => 'Thích nhiều',
        };
    }
}

<?php

namespace App\Enums;

enum ArticleReactionType: string
{
    case Like = 'like';
    case Favorite = 'favorite';

    public function label(): string
    {
        return match ($this) {
            self::Like => 'Thích',
            self::Favorite => 'Yêu thích',
        };
    }
}

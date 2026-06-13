<?php

namespace App\Enums;

enum ActivityEventType: string
{
    case PageView = 'page_view';
    case PostView = 'post_view';
    case WorkshopInterest = 'workshop_interest';
    case Reaction = 'reaction';
    case Comment = 'comment';
    case Login = 'login';
    case Register = 'register';
    case Search = 'search';

    public function label(): string
    {
        return match ($this) {
            self::PageView => 'Lượt xem trang',
            self::PostView => 'Xem bài viết',
            self::WorkshopInterest => 'Quan tâm workshop',
            self::Reaction => 'Reaction',
            self::Comment => 'Bình luận',
            self::Login => 'Đăng nhập',
            self::Register => 'Đăng ký',
            self::Search => 'Tìm kiếm',
        };
    }
}

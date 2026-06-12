<?php

namespace App\Enums;

enum AppNotificationType: string
{
    case PostApproved = 'post_approved';
    case PostRejected = 'post_rejected';
    case CommentReply = 'comment_reply';
    case CommentOnPost = 'comment_on_post';
    case CommentLiked = 'comment_liked';

    public function label(): string
    {
        return match ($this) {
            self::PostApproved => 'Bài được duyệt',
            self::PostRejected => 'Bài bị từ chối',
            self::CommentReply => 'Trả lời bình luận',
            self::CommentOnPost => 'Bình luận mới',
            self::CommentLiked => 'Thích bình luận',
        };
    }
}

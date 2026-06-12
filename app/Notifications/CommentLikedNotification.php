<?php

namespace App\Notifications;

use App\Enums\AppNotificationType;
use App\Models\Article;
use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CommentLikedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Article $article,
        public Comment $comment,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function databaseType(object $notifiable): string
    {
        return AppNotificationType::CommentLiked->value;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => AppNotificationType::CommentLiked->value,
            'message' => 'Có người thích bình luận của bạn',
            'url' => route('community.show', $this->article).'#comment-'.$this->comment->getKey(),
            'article_title' => $this->article->title,
        ];
    }
}

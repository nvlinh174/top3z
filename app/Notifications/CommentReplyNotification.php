<?php

namespace App\Notifications;

use App\Enums\AppNotificationType;
use App\Models\Article;
use App\Models\Comment;
use App\Support\NotificationLink;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CommentReplyNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Article $article,
        public Comment $comment,
        public string $actorName,
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
        return AppNotificationType::CommentReply->value;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => AppNotificationType::CommentReply->value,
            'message' => $this->actorName.' đã trả lời bình luận của bạn',
            'article_title' => $this->article->title,
            'actor_name' => $this->actorName,
            ...NotificationLink::route(
                'community.show',
                ['article' => $this->article->slug],
                fragment: 'comment-'.$this->comment->getKey(),
            ),
        ];
    }
}

<?php

namespace App\Notifications;

use App\Enums\AppNotificationType;
use App\Models\Article;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CommentOnYourPostNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Article $article,
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
        return AppNotificationType::CommentOnPost->value;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => AppNotificationType::CommentOnPost->value,
            'message' => $this->actorName.' đã bình luận bài «'.$this->article->title.'»',
            'url' => route('community.show', $this->article).'#thao-luan',
            'article_title' => $this->article->title,
            'actor_name' => $this->actorName,
        ];
    }
}

<?php

namespace App\Notifications;

use App\Enums\AppNotificationType;
use App\Models\Article;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PostModerationApprovedNotification extends Notification
{
    use Queueable;

    public function __construct(public Article $article) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function databaseType(object $notifiable): string
    {
        return AppNotificationType::PostApproved->value;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => AppNotificationType::PostApproved->value,
            'message' => 'Bài «'.$this->article->title.'» đã được duyệt',
            'url' => route('community.show', $this->article),
            'article_title' => $this->article->title,
        ];
    }
}
